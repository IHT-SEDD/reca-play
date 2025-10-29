<?php

namespace App\Http\Controllers\Creator;

use App\Http\Controllers\Controller;
use App\Jobs\Camera\GetPlaybackUrisJob;
use App\Models\Master\Camera;
use App\Models\Record\RecordingLog;
use App\Models\Session\QrSession;
use App\Models\Session\RecordSession;
use App\Models\Session\SessionCode;
use App\Models\Session\SessionLog;
use App\Services\Support\GetModelService;
use App\Services\Support\ResponseHelperService;
use App\Services\Support\SessionHelperService;
use App\Enums\SessionCodeStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        Log::channel('camera-record')->info('[PREPARE RECORDING] Start checkData', [
            'user_id' => $userId,
            'type' => $type,
            'session_token' => session('qr_session_token')
        ]);

        try {
            $sessionCode = SessionCode::where('session_token', $sessionToken)
                ->where('user_id', $userId)
                ->first();

            if (!$sessionCode) {
                return $this->responseHelperService->errorResponse(
                    'Session code not found or invalid.',
                    404
                );
            }

            $modelClass = $this->getModelService->getData($type);
            if (!$modelClass || !class_exists($modelClass)) {
                if (!$modelClass || !class_exists($modelClass)) {
                    throw new \Exception("Model for {$type} not found");
                }
            }

            if ($type === 'record') {
                $data = $modelClass::where('id', $sessionCode->recording_id)
                    ->where('user_id', $userId)
                    ->where('field_id', $sessionCode->field_id)
                    ->where('session_code_id', $sessionCode->id)
                    ->first();

                if (!$data) {
                    return $this->responseHelperService->errorResponse(
                        'Recording data not found.',
                        404
                    );
                }

                $fieldId = $data->field_id;
                $recordingId = $data->id;

                if (!$recordingId) {
                    return $this->responseHelperService->errorResponse(
                        message: 'No record data found in session.',
                        redirect: url('/my-recording')
                    );
                }

                $autoStopResponse = $this->handleAutoStop($data, $fieldId);
                if ($autoStopResponse) {
                    return $autoStopResponse;
                }

                $cameraData = Camera::where('field_id', $fieldId)->get();

                DB::beginTransaction();

                $streamUrl = $this->livePreview($fieldId);

                DB::commit();

                return $this->responseHelperService->successResponse(
                    message: 'Record data fetched successfully.',
                    data: [
                        'recordData' => $data,
                        'scannedQrData' => $scannedQrData,
                        'cameraData' => $cameraData,
                        'streamUrl' => $streamUrl,
                    ]
                );
            }

            return $this->responseHelperService->errorResponse(
                "Invalid type parameter.",
                400
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::channel('camera-record')->error('[PREPARE RECORDING] Error', [
                'error' => $e->getMessage()
            ]);
            return $this->responseHelperService->errorResponse(
                $e->getMessage(),
                500
            );
        }
    }

    // ============================================================
    // Stop recording (async queued jobs)
    // ============================================================
    public function stopRecording(Request $request)
    {
        $userId = Auth::id();
        $type = $request->query('type');

        if (empty($type)) {
            return $this->responseHelperService->errorResponse(
                'Missing required parameter: type.',
                400
            );
        }

        $sessionToken = session('qr_session_token');

        Log::channel('camera-record')->info("[STOP RECORDING] Start stopRecording", [
            'user_id' => $userId,
            'session_token' => $sessionToken,
        ]);

        try {
            $sessionCode = SessionCode::where('session_token', $sessionToken)
                ->where('user_id', $userId)
                ->first();

            if (!$sessionCode) {
                return $this->responseHelperService->errorResponse(
                    'Session code not found or invalid.',
                    404
                );
            }

            $modelClass = $this->getModelService->getData($type);
            if (!$modelClass || !class_exists($modelClass)) {
                DB::rollBack();
                if (!$modelClass || !class_exists($modelClass)) {
                    throw new \Exception("Model for {$type} not found");
                }
            }

            if ($type === 'record') {
                $data = $modelClass::where('id', $sessionCode->recording_id)
                    ->where('user_id', $userId)
                    ->where('field_id', $sessionCode->field_id)
                    ->where('session_code_id', $sessionCode->id)
                    ->first();

                if (!$data) {
                    return $this->responseHelperService->errorResponse(
                        'Recording data not found.',
                        404
                    );
                }

                if (in_array($data->status, ['done', 'processing'])) {
                    Log::channel('camera-record')->warning("[STOP RECORDING] Recording already processed or in progress", [
                        'recording_id' => $data->id,
                        'current_status' => $data->status,
                    ]);

                    return $this->responseHelperService->otherResponse(
                        status: 'skipped',
                        message: 'Recording already processed or still being processed.',
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
                    false
                );
            }

            return $this->responseHelperService->errorResponse(
                "Invalid type parameter.",
                400
            );
        } catch (\Throwable $e) {
            Log::channel('camera-record')->error('[STOP RECORDING] Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->responseHelperService->errorResponse(
                $e->getMessage(),
                500
            );
        }
    }

    // ============================================================
    // Helpers
    // ============================================================
    private function initializeCameraService(int $fieldId)
    {
        $service = app(\App\Services\Camera\CameraControlService::class);
        $service->initialize($fieldId);
        return $service;
    }

    private function updateRecordingStop($data, $sessionToken, $sessionCodeId)
    {
        RecordingLog::where('recording_id', $data->id)
            ->update(['status' => 'stopped', 'updated_at' => now()]);

        SessionLog::where('recording_id', $data->id)
            ->where('session_token', $sessionToken)
            ->where('session_code_id', $sessionCodeId)
            ->update([
                'end_time' => now(),
                'inactive_at' => now(),
                'status' => 'finished',
            ]);

        SessionCode::findOrFail($sessionCodeId)
            ->update([
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

    private function handleAutoStop($data, $fieldId)
    {
        if ($data->end_time) {
            $endTime = \Carbon\Carbon::parse($data->end_time);
            $now = now();

            if ($now->greaterThanOrEqualTo($endTime)) {
                Log::channel('camera-record')->info("[AUTO STOP] End time reached, stopping recording", [
                    'recording_id' => $data->id,
                    'field_id' => $fieldId,
                    'end_time' => $endTime->toDateTimeString(),
                    'now' => $now->toDateTimeString(),
                ]);

                return $this->finalizeRecording(
                    $data,
                    $fieldId,
                    $data->user_id,
                    $data->session_code_id,
                    session('qr_session_token'),
                    true
                );
            }
        }

        // if ($data->start_time && $data->duration) {
        //     $endTime = \Carbon\Carbon::parse($data->start_time)->addMinutes($data->duration);
        //     if (now()->greaterThanOrEqualTo($endTime)) {
        //         Log::channel('camera-record')->info("[AUTO STOP] Duration exceeded, stopping recording", [
        //             'recording_id' => $data->id,
        //             'field_id' => $fieldId,
        //         ]);

        //         return $this->finalizeRecording($data, $fieldId, $data->user_id, $data->session_code_id, session('qr_session_token'), true);
        //     }
        // }
        return null;
    }

    private function finalizeRecording($recording, $fieldId, $userId, $sessionCodeId, $sessionToken, $isAuto = false)
    {
        $ownTransaction = false;

        try {
            if (DB::transactionLevel() === 0) {
                DB::beginTransaction();
                $ownTransaction = true;
            }

            $cameraService = $this->initializeCameraService($fieldId);
            $cameraService->stopRecording();

            $recording->update([
                'status' => 'processing',
            ]);

            $this->updateRecordingStop($recording, $sessionToken, $sessionCodeId);

            RecordSession::where('user_id', $userId)
                ->where('session_token', $sessionToken)
                ->where('user_id', $userId)
                ->where('recording_id', $recording->id)
                ->delete();

            QrSession::where('user_id', $userId)
                ->where('session_token', $sessionToken)
                ->where('user_id', $userId)
                ->delete();

            $videoName = str_replace(' ', '', $recording->video_name ?? 'recording');

            GetPlaybackUrisJob::dispatch(
                $fieldId,
                $recording->start_time,
                $recording->end_time,
                $userId,
                $videoName,
                $recording->id
            )->onQueue('camera-record-video-search');

            if ($ownTransaction) {
                DB::commit();
            }

            Log::channel('camera-record')->info(
                ($isAuto ? '[AUTO STOP]' : '[STOP RECORDING]') . ' Finalized recording successfully',
                [
                    'recording_id' => $recording->id,
                    'field_id' => $fieldId,
                    'user_id' => $userId,
                    'mode' => $isAuto ? 'auto' : 'manual'
                ]
            );

            return $this->responseHelperService->successResponse(
                message: $isAuto
                    ? 'Recording stopped automatically (duration reached).'
                    : 'Recording stopped manually. Video processing started in background.',
                data: ['recordData' => $recording]
            );
        } catch (\Throwable $e) {
            if ($ownTransaction && DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            if (isset($recording) && $recording->exists) {
                try {
                    $recording->update(['status' => 'failed']);
                } catch (\Throwable $inner) {
                    Log::channel('camera-record')->warning('[FINALIZE RECORDING] Failed to update status after rollback', [
                        'recording_id' => $recording->id,
                        'inner_error' => $inner->getMessage(),
                    ]);
                }
            }

            Log::channel('camera-record')->error('[FINALIZE RECORDING] Exception occurred', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'recording_id' => $recording->id ?? null,
                'user_id' => $userId ?? null,
            ]);

            return $this->responseHelperService->errorResponse(
                'Failed to finalize recording: ' . $e->getMessage(),
                500
            );
        }
    }
}
