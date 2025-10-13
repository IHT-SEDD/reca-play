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
use Illuminate\Support\Facades\Storage;

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
        $id = $recordSession?->recording_id;

        if (!$id) {
            return response()->json([
                'status' => 'error',
                'message' => 'No record data found in session',
                'redirect' => url('/my-recording')
            ]);
        }

        // === Get data model ===
        $modelClass = $this->getModelService->getData($type);
        if (!$modelClass || !class_exists($modelClass)) {
            return response()->json([
                'status' => 'error',
                'message' => "Model for {$type} not found"
            ], 500);
        }

        // === Get data record or stream ===
        $data = $modelClass::find($id);
        if (!$data) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data not found'
            ], 404);
        }

        // === Auto stop logic ===
        if ($type === 'record') {
            $autoStopResponse = $this->handleAutoStop($data, $fieldId);
            if ($autoStopResponse) {
                return $autoStopResponse;
            }
        }

        // === Get data camera ===
        $cameraData = Camera::where('field_id', $fieldId)->get();

        try {
            DB::beginTransaction();
            $streamUrl = $this->livePreview($fieldId);

            // === Init and start recording if $data found ===
            if ($type === 'record' && !$data->start_time) {
                $cameraService = app(\App\Services\Camera\CameraControlService::class);
                $cameraService->initialize($fieldId);

                $success = $cameraService->startRecording();

                if ($success) {
                    $startTime = now()->format('Y-m-d H:i:s');
                    $data->update(['start_time' => $startTime]);

                    RecordingLog::where('recording_id', $data->id)->update([
                        'status' => 'ongoing',
                        'updated_at' => now(),
                    ]);

                    SessionLog::where('recording_id', $data->id)
                        ->where('user_id', $userId)
                        ->where('session_token', $recordSession->session_token)
                        ->where('status', 'in use')
                        ->update([
                            'start_time' => now(),
                            'status' => 'recording',
                            'updated_at' => $startTime,
                        ]);
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
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // ====== stopRecording function ======
    public function stopRecording(Request $request)
    {
        $userId = Auth::id();
        $sessionToken = session('qr_session_token');

        Log::channel('camera-record')->info("[STOP RECORDING] === Start stopRecording ===", [
            'user_id' => $userId,
            'session_token' => $sessionToken,
        ]);

        $recordSession = $this->getRecordSession();
        $scannedQrData = $this->getQrSession();

        $sessionCodeId = SessionCode::where('user_id', $userId)
            ->where('session_token', $sessionToken)
            ->where('status', '!=', 'expired')
            ->latest()
            ->value('id');

        $id = $recordSession?->recording_id;
        $qrData = $scannedQrData?->qr_data ?? [];

        if (is_string($qrData)) {
            $qrData = json_decode($qrData, true);
        }
        $fieldId = $qrData['field_id'] ?? null;

        Log::channel('camera-record')->info("[STOP RECORDING] Session data loaded", [
            'recording_id' => $id,
            'session_code_id' => $sessionCodeId,
            'field_id' => $fieldId,
        ]);

        if (!$id) {
            Log::channel('camera-record')->warning("[STOP RECORDING] No recording data found in session");
            return response()->json([
                'status' => 'error',
                'message' => 'No record data found in session'
            ]);
        }

        $data = Recording::with(['user', 'field', 'camera'])
            ->where('session_code_id', $sessionCodeId)
            ->where('session_token', $sessionToken)
            ->find($id);

        if (!$data) {
            Log::channel('camera-record')->warning("[STOP RECORDING] Recording data not found", [
                'recording_id' => $id
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Data not found'
            ], 404);
        }

        $videoName = strtolower($data->video_name);
        $videoName = str_replace(' ', '_', $videoName);
        $videoName = preg_replace('/[^a-z0-9_\-]/', '', $videoName);

        try {
            DB::beginTransaction();

            Log::channel('camera-record')->info("[STOP RECORDING] Stopping camera", [
                'field_id' => $fieldId,
                'recording_id' => $data->id
            ]);

            // ========== Stop camera recording ==========
            $cameraService = app(\App\Services\Camera\CameraControlService::class);
            $cameraService->initialize($fieldId);
            $cameraService->stopRecording();

            $data->update(['end_time' => now()]);
            Log::channel('camera-record')->info("[STOP RECORDING] Camera stopped successfully");

            RecordingLog::where('recording_id', $data->id)->update([
                'status' => 'stopped',
                'updated_at' => now(),
            ]);

            SessionLog::where('recording_id', $data->id)
                ->where('session_token', $sessionToken)
                ->where('session_code_id', $sessionCodeId)
                ->update([
                    'end_time' => now(),
                    'inactive_at' => now(),
                    'status' => 'finished',
                ]);

            Log::channel('camera-record')->info("[STOP RECORDING] Session and recording logs updated");

            // ========== Search & Download recorded videos ==========
            $recordedSearch = app(\App\Services\Camera\RecordedSearchService::class);
            $recordedSearch->initialize($fieldId, $data->start_time, $data->end_time);

            $playbackUris = $recordedSearch->getAllPlaybackUris();
            Log::channel('camera-record')->info("[STOP RECORDING] Playback URIs fetched", [
                'count' => count($playbackUris)
            ]);

            $savedFiles = [];

            if (!empty($playbackUris)) {
                $savedFiles = $recordedSearch->downloadByPlaybackUris(
                    $playbackUris,
                    $fieldId,
                    $userId,
                    $videoName
                );

                Log::channel('camera-record')->info("[STOP RECORDING] Downloaded files info", [
                    'files' => array_column($savedFiles, 'filename')
                ]);

                foreach ($savedFiles as $file) {
                    $thumbnailPath = $file['thumbnail'] ?? null;
                    $thumbnailFilename = $thumbnailPath ? pathinfo($thumbnailPath, PATHINFO_BASENAME) : null;

                    RecordedVideo::updateOrInsert(
                        ['recording_id' => $data->id, 'video_filename' => $file['filename']],
                        [
                            'video_path' => $file['path'],
                            'video_size' => $file['size'],
                            'thumbnail_path' => $thumbnailPath ? $thumbnailPath : null,
                            'thumbnail_filename' => $thumbnailFilename,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }

            Log::channel('camera-record')->info("[STOP RECORDING] Cleaning up sessions");

            RecordSession::where('user_id', $userId)->where('session_token', $sessionToken)->delete();
            QrSession::where('user_id', $userId)->where('session_token', $sessionToken)->delete();

            DB::commit();

            Log::channel('camera-record')->info("[STOP RECORDING] === Completed successfully ===", [
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
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // ====== Live preview private function ======
    private function livePreview(int $fieldId)
    {
        try {
            $cameraCode = request()->query('camera_code');
            $service = app(\App\Services\Camera\LivePreviewService::class);

            $streamUrl = $cameraCode
                ? $service->getLivePreviewUrlByCode($fieldId, $cameraCode)
                : $service->getLivePreviewUrl($fieldId);

            if (!$streamUrl) {
                throw new \Exception("No stream URL found for this field.");
            }

            return $streamUrl;
        } catch (\Throwable $e) {
            return null;
        }
    }

    // ====== Auto stop record private function ======
    private function handleAutoStop($data, $fieldId)
    {
        if ($data->start_time && $data->duration) {
            $startTime = \Carbon\Carbon::parse($data->start_time);
            $endTime = $startTime->copy()->addMinutes($data->duration);
            $now = now();

            if ($now->greaterThanOrEqualTo($endTime)) {
                $cameraService = app(\App\Services\Camera\CameraControlService::class);
                $cameraService->initialize($fieldId);
                $cameraService->stopRecording();

                $data->update(['end_time' => $now]);

                RecordingLog::where('recording_id', $data->id)->update([
                    'status' => 'finished',
                    'updated_at' => now(),
                ]);

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
        $sessionToken = session('qr_session_token');

        if (!$userId || !$sessionToken) {
            return null;
        }

        return QrSession::with(['qrCode.field.venue'])
            ->where('user_id', $userId)
            ->where('session_token', $sessionToken)
            ->latest('last_active_at')
            ->first();
    }

    private function getRecordSession(): ?RecordSession
    {
        $userId = Auth::id();
        $sessionToken = session('qr_session_token');

        if (!$userId || !$sessionToken) {
            return null;
        }

        $recordingId = SessionCode::where('user_id', $userId)
            ->where('session_token', $sessionToken)
            ->where('status', 'in use')
            ->latest()
            ->value('recording_id');

        $query = RecordSession::where('user_id', $userId);

        if ($recordingId) {
            $query->where('recording_id', $recordingId);
        }

        return $query->latest('created_at')->first();
    }
}
