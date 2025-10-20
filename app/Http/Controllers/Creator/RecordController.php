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

        Log::channel('camera-record')->info('[PREPARE RECORDING] Start checkData', [
            'user_id' => $userId,
            'type' => $type,
            'session_token' => session('qr_session_token')
        ]);

        try {
            $session = $this->checkSession();
            $scannedQrData = $session['qrSession'];
            $recordSession = $session['recordSession'];
            $sessionCodeId = $session['sessionCodeId'];
            $fieldId = $session['fieldId'];
            $sessionToken = $session['sessionToken'];

            $recordingId = $recordSession?->recording_id;

            if (!$recordingId) {
                return $this->errorResponse('No record data found in session', url('/my-recording'));
            }

            // Model lookup
            $modelClass = $this->getModelService->getData($type);
            if (!$modelClass || !class_exists($modelClass)) {
                return $this->errorResponse("Model for {$type} not found", null, 500);
            }

            $data = $modelClass::where('id', $recordingId)
                ->where('user_id', $userId)
                ->where('field_id', $fieldId)
                ->where('session_code_id', $sessionCodeId)
                ->first();

            if (!$data) {
                return $this->errorResponse('Data not found', null, 404);
            }

            // Auto stop if duration exceeded
            if ($type === 'record') {
                $autoStopResponse = $this->handleAutoStop($data, $fieldId);
                if ($autoStopResponse) return $autoStopResponse;
            }

            // Camera list
            $cameraData = Camera::where('field_id', $fieldId)->get();

            DB::beginTransaction();

            $streamUrl = $this->livePreview($fieldId);

            // Start recording if not started
            if ($type === 'record' && empty($data->start_time)) {
                $cameraService = $this->initializeCameraService($fieldId);

                if ($cameraService->startRecording()) {
                    $this->updateRecordingStart($data, $sessionToken, $userId);
                    Log::channel('camera-record')->info('[RECORD] Recording started', [
                        'recording_id' => $data->id,
                        'field_id' => $fieldId,
                    ]);
                } else {
                    throw new \Exception("Failed to start recording");
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'recordData' => $data,
                'scannedQrData' => $scannedQrData,
                'cameraData' => $cameraData,
                'streamUrl' => $streamUrl,
            ]);
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
        $sessionToken = session('qr_session_token');

        Log::channel('camera-record')->info("[STOP RECORDING] Start stopRecording", [
            'user_id' => $userId,
            'session_token' => $sessionToken,
        ]);

        try {
            $session = $this->checkSession();
            $recordSession = $session['recordSession'];
            $sessionCodeId = $session['sessionCodeId'];
            $fieldId = $session['fieldId'];
            $sessionToken = $session['sessionToken'];

            $recordingId = $recordSession?->recording_id;
            if (!$recordingId) {
                return $this->errorResponse('No record data found in session');
            }

            $recording = Recording::find($recordingId);
            if (!$recording) {
                return $this->errorResponse('Recording not found', null, 404);
            }

            if (in_array($recording->status, ['done', 'processing'])) {
                Log::channel('camera-record')->warning("[STOP RECORDING] Recording already processed or in progress", [
                    'recording_id' => $recording->id,
                    'current_status' => $recording->status,
                ]);

                return response()->json([
                    'status' => 'skipped',
                    'message' => 'Recording already processed or still being processed.',
                    'recordData' => $recording,
                ]);
            }

            return $this->finalizeRecording($recording, $fieldId, $userId, $sessionCodeId, $sessionToken, false);
        } catch (\Throwable $e) {
            DB::rollBack();
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
        if ($data->start_time && $data->duration) {
            $endTime = \Carbon\Carbon::parse($data->start_time)->addMinutes($data->duration);
            if (now()->greaterThanOrEqualTo($endTime)) {
                Log::channel('camera-record')->info("[AUTO STOP] Duration exceeded, stopping recording", [
                    'recording_id' => $data->id,
                    'field_id' => $fieldId,
                ]);

                return $this->finalizeRecording($data, $fieldId, $data->user_id, $data->session_code_id, session('qr_session_token'), true);
            }
        }
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
        try {
            DB::beginTransaction();

            $cameraService = $this->initializeCameraService($fieldId);
            $cameraService->stopRecording();

            $recording->update([
                'end_time' => now(),
                'status' => 'processing',
            ]);

            $this->updateRecordingStop($recording, $sessionToken, $sessionCodeId);
            RecordingLog::where('recording_id', $recording->id)
                ->update(['status' => 'stopped', 'updated_at' => now()]);

            // Bersihkan sesi
            RecordSession::where('user_id', $userId)->where('session_token', $sessionToken)->delete();
            QrSession::where('user_id', $userId)->where('session_token', $sessionToken)->delete();

            // Queue background job untuk unduh video
            $videoName = str_replace(' ', '', $recording->video_name ?? 'recording');
            GetPlaybackUrisJob::dispatch(
                $fieldId,
                $recording->start_time,
                now(),
                $userId,
                $videoName,
                $recording->id
            )->onQueue('camera-record-video-search');

            DB::commit();

            Log::channel('camera-record')->info(($isAuto ? '[AUTO STOP]' : '[STOP RECORDING]') . ' Finalized recording', [
                'recording_id' => $recording->id,
                'field_id' => $fieldId,
                'user_id' => $userId,
                'mode' => $isAuto ? 'auto' : 'manual'
            ]);

            return response()->json([
                'status' => 'success',
                'message' => $isAuto
                    ? 'Recording stopped automatically (duration reached).'
                    : 'Recording stopped manually. Video processing started in background.',
                'recordData' => $recording
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::channel('camera-record')->error('[FINALIZE RECORDING] Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse($e->getMessage(), null, 500);
        }
    }
}
