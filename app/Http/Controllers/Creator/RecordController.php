<?php

namespace App\Http\Controllers\Creator;

use App\Http\Controllers\Controller;
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

    // ====== Initialize services ======
    public function __construct(GetModelService $getModelService)
    {
        $this->getModelService = $getModelService;
    }

    // ====== Show record page ======
    public function recordPage()
    {
        return view('pages.creator.record.index');
    }

    // ====== Check data record or streaming ======
    public function checkData(Request $request)
    {
        $userId = Auth::id();
        $type = $request->query('type');

        $scannedQrData = $this->getQrSession();
        $recordSession = $this->getRecordSession();

        $fieldId = $scannedQrData?->qrCode?->field_id;
        $recordingId = $recordSession?->recording_id;

        if (!$recordingId) {
            return $this->errorResponse('No record data found in session', url('/my-recording'));
        }

        // === Get data model ===
        $modelClass = $this->getModelService->getData($type);
        if (!$modelClass || !class_exists($modelClass)) {
            return $this->errorResponse("Model for {$type} not found", null, 500);
        }

        // === Get data record or stream ===
        $data = $modelClass::find($recordingId);
        if (!$data) return $this->errorResponse('Data not found', null, 404);

        // === Auto stop logic ===
        if ($type === 'record') {
            $autoStopResponse = $this->handleAutoStop($data, $fieldId);
            if ($autoStopResponse) return $autoStopResponse;
        }

        // === Get data camera ===
        $cameraData = Camera::where('field_id', $fieldId)->get();

        try {
            DB::beginTransaction();
            $streamUrl = $this->livePreview($fieldId);

            if ($type === 'record' && !$data->start_time) {
                $cameraService = $this->initializeCameraService($fieldId);

                if ($cameraService->startRecording()) {
                    $this->updateRecordingStart($data, $recordSession->session_token, $userId);
                } else {
                    throw new \Exception("Failed to start recording on one or more cameras");
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
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e->getMessage(), null, 500);
        }
    }

    // ====== stopRecording function ======
    public function stopRecording(Request $request)
    {
        $userId = Auth::id();
        $sessionToken = session('qr_session_token');

        Log::channel('camera-record')->info("[STOP RECORDING] Start stopRecording", [
            'user_id' => $userId,
            'session_token' => $sessionToken,
        ]);

        $recordSession = $this->getRecordSession();
        $scannedQrData = $this->getQrSession();
        $sessionCodeId = $this->getSessionCodeId($userId, $sessionToken);

        $recordingId = $recordSession?->recording_id;
        $fieldId = $this->extractFieldId($scannedQrData);

        if (!$recordingId) return $this->errorResponse('No record data found in session');

        $data = Recording::with(['user', 'field', 'camera'])
            ->where('session_code_id', $sessionCodeId)
            ->where('session_token', $sessionToken)
            ->find($recordingId);

        if (!$data) return $this->errorResponse('Data not found', null, 404);

        $videoName = $this->formatVideoName($data->video_name);

        try {
            DB::beginTransaction();

            $cameraService = $this->initializeCameraService($fieldId);
            $cameraService->stopRecording();

            $data->update(['end_time' => now()]);
            $this->updateRecordingStop($data, $sessionToken, $sessionCodeId);

            $savedFiles = $this->downloadAndSaveVideos($fieldId, $data, $videoName, $userId);

            // Cleanup sessions
            RecordSession::where('user_id', $userId)->where('session_token', $sessionToken)->delete();
            QrSession::where('user_id', $userId)->where('session_token', $sessionToken)->delete();

            DB::commit();

            Log::channel('camera-record')->info("[STOP RECORDING] Completed successfully", [
                'recording_id' => $data->id,
                'downloaded_files' => count($savedFiles)
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Recording stopped and video(s) downloaded',
                'recordData' => $data->toArray(),
                'downloadedFiles' => $savedFiles
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('camera-record')->error("[STOP RECORDING] Exception caught", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->errorResponse($e->getMessage(), null, 500);
        }
    }

    private function initializeCameraService(int $fieldId)
    {
        $service = app(\App\Services\Camera\CameraControlService::class);
        $service->initialize($fieldId);
        return $service;
    }

    private function updateRecordingStart($data, $sessionToken, $userId)
    {
        $startTime = now()->format('Y-m-d H:i:s');
        $data->update(['start_time' => $startTime]);

        RecordingLog::where('recording_id', $data->id)
            ->update(['status' => 'ongoing', 'updated_at' => now()]);

        SessionLog::where('recording_id', $data->id)
            ->where('user_id', $userId)
            ->where('session_token', $sessionToken)
            ->where('status', 'in use')
            ->update([
                'start_time' => now(),
                'status' => 'recording',
                'updated_at' => $startTime,
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

    private function downloadAndSaveVideos($fieldId, $data, $videoName, $userId)
    {
        $recordedSearch = app(\App\Services\Camera\RecordedSearchService::class);
        $recordedSearch->initialize($fieldId, $data->start_time, $data->end_time);

        $playbackUris = $recordedSearch->getAllPlaybackUris();
        $savedFiles = [];

        if (!empty($playbackUris)) {
            $savedFiles = $recordedSearch->downloadByPlaybackUris($playbackUris, $fieldId, $userId, $videoName);

            foreach ($savedFiles as $file) {
                $thumbnailPath = $file['thumbnail'] ?? null;
                $thumbnailFilename = $thumbnailPath ? pathinfo($thumbnailPath, PATHINFO_BASENAME) : null;

                RecordedVideo::updateOrInsert(
                    ['recording_id' => $data->id, 'video_filename' => $file['filename']],
                    [
                        'video_path' => $file['path'],
                        'video_size' => $file['size'],
                        'thumbnail_path' => $thumbnailPath,
                        'thumbnail_filename' => $thumbnailFilename,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        return $savedFiles;
    }

    private function formatVideoName(string $name): string
    {
        return strtolower(preg_replace('/[^a-z0-9_\-]/', '', str_replace(' ', '_', $name)));
    }

    private function extractFieldId($scannedQrData): ?int
    {
        $qrData = $scannedQrData?->qr_data ?? [];
        if (is_string($qrData)) $qrData = json_decode($qrData, true);
        return $qrData['field_id'] ?? null;
    }

    private function getSessionCodeId($userId, $sessionToken)
    {
        return SessionCode::where('user_id', $userId)
            ->where('session_token', $sessionToken)
            ->where('status', '!=', 'expired')
            ->latest()
            ->value('id');
    }

    private function errorResponse(string $message, ?string $redirect = null, int $code = 400)
    {
        $response = ['status' => 'error', 'message' => $message];
        if ($redirect) $response['redirect'] = $redirect;
        return response()->json($response, $code);
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
                $cameraService = $this->initializeCameraService($fieldId);
                $cameraService->stopRecording();

                $data->update(['end_time' => now()]);
                RecordingLog::where('recording_id', $data->id)
                    ->update(['status' => 'finished', 'updated_at' => now()]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Recording has ended automatically.',
                    'recordData' => $data,
                ]);
            }
        }

        return null;
    }

    private function getQrSession(): ?QrSession
    {
        $userId = Auth::id();
        return QrSession::with(['qrCode.field.venue'])
            ->where('user_id', $userId)
            ->latest('last_active_at')
            ->first();
    }

    private function getRecordSession(): ?RecordSession
    {
        $userId = Auth::id();
        $sessionToken = session('qr_session_token');
        if (!$userId || !$sessionToken) return null;

        $recordingId = SessionCode::where('user_id', $userId)
            ->where('status', '=', 'in use')
            ->whereNotNull('recording_id')
            ->value('recording_id');

        $query = RecordSession::where('user_id', $userId);
        if ($recordingId) $query->where('recording_id', $recordingId);

        return $query->latest('created_at')->first();
    }
}
