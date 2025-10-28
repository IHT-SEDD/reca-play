<?php

namespace App\Http\Controllers\Creator;

use App\Http\Controllers\Controller;
use App\Jobs\Camera\GetPlaybackUrisJob;
use App\Jobs\Camera\InsertRecordedVideoJob;
use App\Jobs\Camera\ThumbnailVideoJob;
use App\Jobs\Camera\TrimVideoJob;
use App\Models\Master\Camera;
use App\Models\Record\RecordedVideo;
use App\Models\Record\Recording;
use App\Models\Record\RecordingLog;
use App\Models\Session\QrSession;
use App\Models\Session\RecordSession;
use App\Models\Session\SessionCode;
use App\Models\Session\SessionLog;
use App\Services\Support\GetModelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecordController extends Controller
{
    protected GetModelService $getModelService;

    // ===============================
    // INIT SERVICE
    // ===============================
    public function __construct(GetModelService $getModelService)
    {
        $this->getModelService = $getModelService;
    }

    // ===============================
    // SHOW RECORD PAGE
    // ===============================
    public function recordPage()
    {
        return view('pages.creator.record.index');
    }

    // ===============================
    // CHECK RECORD DATA OR STREAM
    // ===============================
    public function checkData(Request $request)
    {
        $userId = Auth::id();
        $type = $request->query('type');
        $sessionToken = session('qr_session_token');
        $sessionQrToken = session('qr_token');
        $scannedQrData = $this->getActiveQrSession();

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
                return $this->errorResponse('Session code not found or invalid.', null, 404);
            }

            $modelClass = $this->getModelService->getData($type);
            if (!$modelClass || !class_exists($modelClass)) {
                return $this->errorResponse("Model for {$type} not found", null, 500);
            }

            if ($type === 'record') {
                $data = $modelClass::where('id', $sessionCode->recording_id)
                    ->where('user_id', $userId)
                    ->where('field_id', $sessionCode->field_id)
                    ->where('session_code_id', $sessionCode->id)
                    ->first();

                if (!$data) {
                    return $this->errorResponse('Recording data not found.', null, 404);
                }

                $fieldId = $data->field_id;
                $recordingId = $data->id;

                if (!$recordingId) {
                    return $this->errorResponse('No record data found in session.', url('/my-recording'));
                }

                $autoStopResponse = $this->handleAutoStop($data, $fieldId);
                if ($autoStopResponse) {
                    return $autoStopResponse;
                }

                $cameraData = Camera::where('field_id', $fieldId)->get();

                DB::beginTransaction();

                $streamUrl = $this->livePreview($fieldId);

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'recordData' => $data,
                    'scannedQrData' => $scannedQrData,
                    'cameraData' => $cameraData,
                    'streamUrl' => $streamUrl,
                ]);
            }

            // $session = $this->checkSession();
            // $scannedQrData = $session['qrSession'];
            // $recordSession = $session['recordSession'];
            // $sessionCodeId = $session['sessionCodeId'];
            // $fieldId = $session['fieldId'];
            // $sessionToken = $session['sessionToken'];

            // Start recording if not started
            // if ($type === 'record' && empty($data->start_time)) {
            //     $cameraService = $this->initializeCameraService($fieldId);

            //     if ($cameraService->startRecording()) {
            //         $this->updateRecordingStart($data, $sessionToken, $userId);
            //         Log::channel('camera-record')->info('[RECORD] Recording started', [
            //             'recording_id' => $data->id,
            //             'field_id' => $fieldId,
            //         ]);
            //     } else {
            //         throw new \Exception("Failed to start recording");
            //     }
            // }

            return $this->errorResponse("Invalid type parameter.", null, 400);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::channel('camera-record')->error('[PREPARE RECORDING] Error', [
                'error' => $e->getMessage()
            ]);
            return $this->errorResponse($e->getMessage(), null, 500);
        }
    }

    // ===============================
    // STOP RECORDING (async queued jobs)
    // ===============================
    public function stopRecording(Request $request)
    {
        $userId = Auth::id();
        $type = $request->query('type');

        if (empty($type)) {
            return $this->errorResponse('Missing required parameter: type.', null, 400);
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
                return $this->errorResponse('Session code not found or invalid.', null, 404);
            }

            $modelClass = $this->getModelService->getData($type);
            if (!$modelClass || !class_exists($modelClass)) {
                DB::rollBack();
                return $this->errorResponse("Model for {$type} not found", null, 500);
            }

            if ($type === 'record') {
                $data = $modelClass::where('id', $sessionCode->recording_id)
                    ->where('user_id', $userId)
                    ->where('field_id', $sessionCode->field_id)
                    ->where('session_code_id', $sessionCode->id)
                    ->first();

                if (!$data) {
                    return $this->errorResponse('Recording data not found.', null, 404);
                }

                // if (in_array($recording->status, ['done', 'processing'])) {
                if (in_array($data->status, ['done', 'processing'])) {
                    Log::channel('camera-record')->warning("[STOP RECORDING] Recording already processed or in progress", [
                        'recording_id' => $data->id,
                        'current_status' => $data->status,
                    ]);

                    return response()->json([
                        'status' => 'skipped',
                        'message' => 'Recording already processed or still being processed.',
                        'recordData' => $data,
                    ]);
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
            // $session = $this->checkSession();
            // $recordSession = $session['recordSession'];
            // $sessionCodeId = $session['sessionCodeId'];
            // $fieldId = $session['fieldId'];
            // $sessionToken = $session['sessionToken'];

            // $recordingId = $recordSession?->recording_id;
            // if (!$recordingId) {
            //     return $this->errorResponse('No record data found in session');
            // }

            // $recording = Recording::find($recordingId);
            // if (!$recording) {
            //     return $this->errorResponse('Recording not found', null, 404);
            // }

            return $this->errorResponse("Invalid type parameter.", null, 400);
        } catch (\Throwable $e) {
            Log::channel('camera-record')->error('[STOP RECORDING] Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse($e->getMessage(), null, 500);
        }
    }

    // ===============================
    // HELPERS
    // ===============================

    private function initializeCameraService(int $fieldId)
    {
        $service = app(\App\Services\Camera\CameraControlService::class);
        $service->initialize($fieldId);
        return $service;
    }

    private function updateRecordingStart($data, $sessionToken, $userId)
    {
        $data->update(['start_time' => now()]);

        RecordingLog::where('recording_id', $data->id)
            ->update(['status' => 'ongoing', 'updated_at' => now()]);

        SessionLog::where('recording_id', $data->id)
            ->where('user_id', $userId)
            ->where('session_token', $sessionToken)
            ->where('status', 'in use')
            ->update([
                'start_time' => now(),
                'status' => 'recording',
                'updated_at' => now(),
            ]);
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
    }

    private function errorResponse(string $message, ?string $redirect = null)
    {
        $response = ['status' => 'error', 'message' => $message];
        if ($redirect) $response['redirect'] = $redirect;
        return response()->json($response);
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

    private function checkSession(): array
    {
        $userId = Auth::id();
        $sessionToken = session('qr_session_token');

        $qrSession = QrSession::with(['qrCode.field.venue'])
            ->where('user_id', $userId)
            ->latest('last_active_at')
            ->first();

        $fieldId = $qrSession?->qrCode?->field_id ?? null;
        $recordSessionQuery = RecordSession::where('user_id', $userId);
        $recordSession = $recordSessionQuery->latest('created_at')->first();

        $sessionCodeId = SessionCode::where('user_id', $userId)
            ->where('field_id', $fieldId)
            ->where('status', '!=', 'expired')
            ->where('session_token', $sessionToken)
            ->latest('created_at')
            ->value('id');

        return [
            'qrSession' => $qrSession,
            'recordSession' => $recordSession,
            'sessionCodeId' => $sessionCodeId,
            'fieldId' => $fieldId,
            'sessionToken' => $sessionToken,
        ];
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

            RecordingLog::where('recording_id', $recording->id)
                ->update(['status' => 'stopped', 'updated_at' => now()]);

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

            return response()->json([
                'status' => 'success',
                'message' => $isAuto
                    ? 'Recording stopped automatically (duration reached).'
                    : 'Recording stopped manually. Video processing started in background.',
                'recordData' => $recording,
            ]);
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

            return $this->errorResponse('Failed to finalize recording: ' . $e->getMessage(), null, 500);
        }
    }

    private function getActiveQrSession(bool $requireActiveSession = true): ?QrSession
    {
        $userId = Auth::id();
        $sessionToken = session('qr_session_token');
        $sessionQrToken = session('qr_token');

        if (!$userId || !$sessionToken) return null;

        $query = QrSession::with(['qrCode.field.venue'])
            ->where('user_id', $userId)
            ->where('session_token', $sessionToken)
            ->where('qr_token', $sessionQrToken)
            ->latest();

        if ($requireActiveSession) {
            $query->whereNotNull('session_token');
            $query->whereNotNull('qr_token');
        }

        return $query->first();
    }
}
