<?php

namespace App\Services\Creator;

use App\Jobs\Camera\GetPlaybackUrisJob;
use App\Models\Session\QrSession;
use App\Models\Session\SessionCode;
use App\Models\Session\SessionLog;
use App\Models\Session\RecordSession;
use App\Models\Session\StreamSession;
use App\Models\Record\RecordingLog;
use App\Models\Stream\StreamingLog;
use App\Enums\RecordingStatus;
use App\Enums\StreamingStatus;
use App\Enums\SessionCodeStatus;
use App\Enums\RecordingLogStatus;
use App\Enums\SessionLogStatus;
use App\Enums\StreamingLogStatus;
use App\Services\Camera\LivePreviewService;
use App\Services\Support\GetModelService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UtilityService
{
  // ============================================================
  // Validate type
  // ============================================================
  public function isValidType(?string $type): bool
  {
    return in_array($type, ['record', 'stream']);
  }

  // ============================================================
  // Get data by type
  // ============================================================
  public function getDataByType(string $type, SessionCode $sessionCode, int $userId)
  {
    $modelClass = app(GetModelService::class)->getData($type);

    if (!$modelClass || !class_exists($modelClass)) {
      throw new \Exception("Model for {$type} not found");
    }

    $idField = $type === 'record' ? 'recording_id' : 'streaming_id';

    return $modelClass::where('id', $sessionCode->$idField)
      ->where('user_id', $userId)
      ->where('field_id', $sessionCode->field_id)
      ->where('session_code_id', $sessionCode->id)
      ->first();
  }

  // ============================================================
  // Get URL live preview camera
  // ============================================================
  public function livePreview(int $fieldId)
  {
    try {
      $service = app(LivePreviewService::class);

      return $service->getLivePreviewUrl($fieldId);
    } catch (\Throwable $e) {
      Log::channel('utility-service')->error('[LIVE PREVIEW] Error', [
        'error' => $e->getMessage()
      ]);
      return null;
    }
  }

  // ============================================================
  // Get URL live preview change camera
  // ============================================================
  public function changeCamLivePreview(int $fieldId, string $camCode)
  {
    try {
      $service = app(LivePreviewService::class);

      return $service->getLivePreviewUrlByCode($fieldId, $camCode);
    } catch (\Throwable $e) {
      Log::channel('utility-service')->error('[CHANGE CAM LIVE PREVIEW] Error', [
        'error' => $e->getMessage()
      ]);
      return null;
    }
  }

  // ============================================================
  // Handle auto stop
  // ============================================================
  public function handleAutoStop($data, $fieldId, $type)
  {
    if ($data->end_time && now()->greaterThanOrEqualTo($data->end_time)) {
      Log::channel('camera-' . $type)->info("[AUTO STOP] End time reached, stopping", [
        'id' => $data->id,
        'field_id' => $fieldId,
      ]);

      return $this->finalizeRecording(
        $data,
        $fieldId,
        $data->user_id,
        $data->session_code_id,
        session('qr_session_token'),
        true,
        $type
      );
    }

    return null;
  }

  // ============================================================
  // Finalization recording
  // ============================================================
  public function finalizeRecording($data, $fieldId, $userId, $sessionCodeId, $sessionToken, $isAuto = false, $type)
  {
    $ownTransaction = false;
    $config = $this->getConfigByType($type);

    try {
      if (DB::transactionLevel() === 0) {
        DB::beginTransaction();
        $ownTransaction = true;
      }

      $cameraService = $this->initializeCameraService($fieldId);
      $cameraService->stopRecording();

      $data->update(['status' => $config['statusEnum']]);
      $this->updateRecordingStop($data, $sessionToken, $sessionCodeId, $type);

      $config['sessionModel']::where('user_id', $userId)
        ->where('session_token', $sessionToken)
        ->where($config['idField'], $data->id)
        ->delete();

      QrSession::where('user_id', $userId)
        ->where('session_token', $sessionToken)
        ->delete();

      $videoName = str_replace(' ', '', $data->video_name ?? $config['videoName']);

      GetPlaybackUrisJob::dispatch(
        $fieldId,
        $data->start_time,
        $data->end_time,
        $userId,
        $videoName,
        $data->id
      )->onQueue('camera-record-video-search');

      if ($ownTransaction) {
        DB::commit();
      }

      Log::channel($config['logChannel'])->info(
        ($isAuto ? '[AUTO STOP]' : '[STOP ' . strtoupper($type) . ']') . ' Finalized successfully',
        [
          'id' => $data->id,
          'field_id' => $fieldId,
          'user_id' => $userId,
          'mode' => $isAuto ? 'auto' : 'manual',
        ]
      );

      return [
        'status' => 'success',
        'message' => $isAuto
          ? ucfirst($type) . ' stopped automatically.'
          : ucfirst($type) . ' stopped manually. Video processing started.',
        'data' => $data,
      ];
    } catch (\Throwable $e) {
      if ($ownTransaction && DB::transactionLevel() > 0) {
        DB::rollBack();
      }

      Log::channel($config['logChannel'])->error('[FINALIZE ' . strtoupper($type) . '] Exception', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
      ]);

      return [
        'status' => 'error',
        'message' => 'Failed to finalize ' . $type . ': ' . $e->getMessage(),
      ];
    }
  }

  // ============================================================
  // Get config by type
  // ============================================================
  public function getConfigByType(string $type): array
  {
    return match ($type) {
      'record' => [
        'statusEnum' => RecordingStatus::Processing,
        'failedEnum' => RecordingStatus::Failed,
        'sessionModel' => RecordSession::class,
        'logModel' => RecordingLog::class,
        'logEnum' => RecordingLogStatus::Stopped,
        'logChannel' => 'camera-record',
        'videoName' => 'recording',
        'idField' => 'recording_id',
      ],
      'stream' => [
        'statusEnum' => StreamingStatus::Processing,
        'failedEnum' => StreamingStatus::Failed,
        'sessionModel' => StreamSession::class,
        'logModel' => StreamingLog::class,
        'logEnum' => StreamingLogStatus::Stopped,
        'logChannel' => 'camera-stream',
        'videoName' => 'streaming',
        'idField' => 'streaming_id',
      ],
    };
  }

  // ============================================================
  // Initialize camera service
  // ============================================================
  public function initializeCameraService(int $fieldId)
  {
    $service = app(\App\Services\Camera\CameraControlService::class);
    $service->initialize($fieldId);
    return $service;
  }

  // ============================================================
  // Update recording stop
  // ============================================================
  public function updateRecordingStop($data, $sessionToken, $sessionCodeId, $type)
  {
    $config = $this->getConfigByType($type);
    $idField = $config['idField'];

    $config['logModel']::where($idField, $data->id)->update([
      'status' => $config['logEnum'],
      'updated_at' => now(),
    ]);

    SessionLog::where($idField, $data->id)
      ->where('session_token', $sessionToken)
      ->where('session_code_id', $sessionCodeId)
      ->update([
        'end_time' => now(),
        'inactive_at' => now(),
        'status' => SessionLogStatus::Finished,
      ]);

    SessionCode::findOrFail($sessionCodeId)->update([
      'status' => SessionCodeStatus::Done,
    ]);
  }
}
