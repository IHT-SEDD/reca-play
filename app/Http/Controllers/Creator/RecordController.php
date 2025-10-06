<?php

namespace App\Http\Controllers\Creator;

use App\Http\Controllers\Controller;
use App\Models\Master\Camera;
use App\Models\Record\RecordedVideo;
use App\Models\Record\Recording;
use App\Models\Record\RecordingLog;
use App\Models\Session\QrSession;
use App\Models\Session\RecordSession;
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
        $recordSession = $this->getRecordSession();
        $scannedQrData = $this->getQrSession();

        $id = $recordSession?->recording_id;

        $qrData = $scannedQrData?->qr_data ?? [];
        if (is_string($qrData)) {
            $qrData = json_decode($qrData, true);
        }
        $fieldId = $qrData['field_id'] ?? null;

        if (!$id) {
            return response()->json([
                'status' => 'error',
                'message' => 'No record data found in session'
            ]);
        }

        $data = Recording::with(['user', 'field', 'camera'])->find($id);
        if (!$data) {
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

            // ========== Stop camera recording ==========
            $cameraService = app(\App\Services\Camera\CameraControlService::class);
            $cameraService->initialize($fieldId);
            $cameraService->stopRecording();

            $data->update(['end_time' => now()]);

            RecordingLog::where('recording_id', $data->id)->update([
                'status' => 'stopped',
                'updated_at' => now(),
            ]);

            // ========== Search & Download recorded videos ==========
            $recordedSearch = app(\App\Services\Camera\RecordedSearchService::class);
            $recordedSearch->initialize($fieldId, $data->start_time, $data->end_time);

            $playbackUris = $recordedSearch->getAllPlaybackUris();
            $savedFiles = [];

            if (!empty($playbackUris)) {
                $savedFiles = $recordedSearch->downloadByPlaybackUris(
                    $playbackUris,
                    $fieldId,
                    $userId,
                    $videoName
                );

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

            RecordSession::where('user_id', $userId)->delete();
            QrSession::where('user_id', $userId)->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Recording stopped and video(s) downloaded',
                'recordData' => $data->toArray(),
                'downloadedFiles' => $savedFiles
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('camera-record')->error("[STOP RECORDING CONTROLLER] Exception: " . $e->getMessage());
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

        return QrSession::with(['qrCode.field.venue'])
            ->where('user_id', $userId)
            ->latest()
            ->first();
    }

    private function getRecordSession(): ?RecordSession
    {
        $userId = Auth::id();

        return RecordSession::where('user_id', $userId)
            ->latest()
            ->first();
    }
}
