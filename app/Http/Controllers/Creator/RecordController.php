<?php

namespace App\Http\Controllers\Creator;

use App\Http\Controllers\Controller;

use App\Models\Master\Camera;
use App\Models\Record\RecordingLog;
use App\Models\Session\QrSession;
use App\Models\Session\RecordSession;
use App\Models\Session\SessionCode;
use App\Models\Session\SessionLog;

use App\Enums\RecordingLogStatus;
use App\Enums\RecordingStatus;
use App\Enums\SessionCodeStatus;
use App\Enums\StreamingLogStatus;
use App\Enums\StreamingStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Services\Support\GetModelService;
use App\Services\Support\ResponseHelperService;
use App\Services\Support\SessionHelperService;

use App\Jobs\Camera\GetPlaybackUrisJob;
use App\Models\Session\StreamSession;
use App\Models\Stream\StreamingLog;

class RecordController extends Controller
{
    // ============================================================
    // Init service
    // ============================================================
    protected GetModelService $getModelService;
    protected SessionHelperService $sessionHelperService;
    protected ResponseHelperService $responseHelperService;

    public function __construct(
        GetModelService $getModelService,
        SessionHelperService $sessionHelperService,
        ResponseHelperService $responseHelperService
    ) {
        $this->getModelService = $getModelService;
        $this->sessionHelperService = $sessionHelperService;
        $this->responseHelperService = $responseHelperService;
    }

    // ============================================================
    // Show view of record
    // ============================================================
    public function recordPage()
    {
        return view('pages.creator.record.index');
    }

    // ============================================================
    // Check data record or stream
    // ============================================================
    public function checkData(Request $request)
    {
        $userId = Auth::id();
        $type = $request->query('type');
        $sessionToken = session('qr_session_token');
        $sessionQrToken = session('qr_token');
        $scannedQrData = $this->sessionHelperService->getActiveQrSession();

        Log::channel('camera-' . $type)->info('[PREPARE ' . strtoupper($type) . '] Start checkData', [
            'user_id' => $userId,
            'type' => $type,
            'session_token' => $sessionToken,
        ]);

        if (!$this->isValidType($type)) {
            return $this->responseHelperService->errorResponse("Invalid type parameter.", 400);
        }

        try {
            $sessionCode = SessionCode::where('session_token', $sessionToken)
                ->where('user_id', $userId)
                ->first();

            $data = $this->getDataByType($type, $sessionCode, $userId);
            if (!$data) {
                return $this->responseHelperService->errorResponse(
                    ucfirst($type) . ' data not found.',
                    404
                );
            }

            $fieldId = $data->field_id;

            if ($autoStopResponse = $this->handleAutoStop($data, $fieldId, $type)) {
                return $autoStopResponse;
            }

            $cameraData = Camera::where('field_id', $fieldId)->get();

            DB::beginTransaction();
            $streamUrl = $this->livePreview($fieldId);
            DB::commit();

            return $this->responseHelperService->successResponse(
                message: ucfirst($type) . ' data fetched successfully.',
                data: [
                    'recordData' => $data,
                    'scannedQrData' => $scannedQrData,
                    'cameraData' => $cameraData,
                    'streamUrl' => $streamUrl,
                ]
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::channel('camera-' . $type)->error('[PREPARE ' . strtoupper($type) . '] Error', [
                'error' => $e->getMessage(),
            ]);

            return $this->responseHelperService->errorResponse($e->getMessage(), 500);
        }
    }

    // ============================================================
    // Stop recording (async queued jobs)
    // ============================================================
    public function stopRecording(Request $request)
    {
        $userId = Auth::id();
        $type = $request->query('type');
        $sessionToken = session('qr_session_token');

        if (!$this->isValidType($type)) {
            return $this->responseHelperService->errorResponse('Invalid or missing type parameter.', 400);
        }

        Log::channel('camera-' . $type)->info("[STOP {$type}] Start stopRecording", [
            'user_id' => $userId,
            'session_token' => $sessionToken,
        ]);

        try {
            $sessionCode = SessionCode::where('session_token', $sessionToken)
                ->where('user_id', $userId)
                ->first();

            $data = $this->getDataByType($type, $sessionCode, $userId);
            if (!$data) {
                return $this->responseHelperService->errorResponse(
                    ucfirst($type) . ' data not found.',
                    404
                );
            }

            if (in_array($data->status, ['done', 'processing'])) {
                Log::channel('camera-' . $type)->warning("[STOP {$type}] Already processed or in progress", [
                    'id' => $data->id,
                    'current_status' => $data->status,
                ]);

                return $this->responseHelperService->otherResponse(
                    status: 'skipped',
                    message: ucfirst($type) . ' already processed or still being processed.',
                    data: ['recordData' => $data],
                    code: 200
                );
            }

            return $this->finalizeRecording(
                $data,
                $data->field_id,
                $userId,
                $sessionCode->id,
                $sessionToken,
                false,
                $type
            );
        } catch (\Throwable $e) {
            Log::channel('camera-' . $type)->error('[STOP ' . strtoupper($type) . '] Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->responseHelperService->errorResponse($e->getMessage(), 500);
        }
    }

    // ============================================================
    // Helpers
    // ============================================================
    private function isValidType(?string $type): bool
    {
        return in_array($type, ['record', 'stream']);
    }

    private function getDataByType(string $type, SessionCode $sessionCode, int $userId)
    {
        $modelClass = $this->getModelService->getData($type);
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

    private function getConfigByType(string $type): array
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

    private function initializeCameraService(int $fieldId)
    {
        $service = app(\App\Services\Camera\CameraControlService::class);
        $service->initialize($fieldId);
        return $service;
    }

    private function updateRecordingStop($data, $sessionToken, $sessionCodeId, $type)
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
                'status' => 'finished',
            ]);

        SessionCode::findOrFail($sessionCodeId)->update([
            'status' => SessionCodeStatus::Done,
        ]);
    }

    private function livePreview(int $fieldId)
    {
        try {
            $cameraCode = request()->query('camera_code');
            $service = app(\App\Services\Camera\LivePreviewService::class);

            return $cameraCode
                ? $service->getLivePreviewUrlByCode($fieldId, $cameraCode)
                : $service->getLivePreviewUrl($fieldId);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function handleAutoStop($data, $fieldId, $type)
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

    private function finalizeRecording($data, $fieldId, $userId, $sessionCodeId, $sessionToken, $isAuto = false, $type)
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

            return $this->responseHelperService->successResponse(
                message: $isAuto
                    ? ucfirst($type) . ' stopped automatically (duration reached).'
                    : ucfirst($type) . ' stopped manually. Video processing started in background.',
                data: ['recordData' => $data]
            );
        } catch (\Throwable $e) {
            if ($ownTransaction && DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            if (isset($data) && $data->exists) {
                try {
                    $data->update(['status' => $config['failedEnum']]);
                } catch (\Throwable $inner) {
                    Log::channel($config['logChannel'])->warning('[FINALIZE] Failed to update status after rollback', [
                        'id' => $data->id,
                        'inner_error' => $inner->getMessage(),
                    ]);
                }
            }

            Log::channel($config['logChannel'])->error('[FINALIZE ' . strtoupper($type) . '] Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'id' => $data->id ?? null,
                'user_id' => $userId ?? null,
            ]);

            return $this->responseHelperService->errorResponse(
                'Failed to finalize ' . $type . ': ' . $e->getMessage(),
                500
            );
        }
    }
}
