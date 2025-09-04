<?php

namespace App\Http\Controllers;

use App\Services\CameraControlService;
use App\Services\CameraFileService;
use App\Services\VideoProcessingService;
use App\Services\CameraLiveService; // punyamu sendiri untuk WHEP URL
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TestingController extends Controller
{
    protected CameraLiveService $liveService;

    public function __construct(CameraLiveService $liveService)
    {
        $this->liveService = $liveService;
    }

    public function livePreview()
    {
        try {
            $streamUrl = $this->liveService->getLiveStreamUrl();
            return view('testing', compact('streamUrl'));
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to start live preview.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function start(CameraControlService $control): JsonResponse
    {
        try {
            if ($control->startRecording()) {
                // Simpan waktu mulai di session
                session(['camera_start_time' => now()]);

                return response()->json([
                    'status' => 'success',
                    'start_time' => now()->toDateTimeString()
                ]);
            }
            Log::error("Failed to start recording: CameraControlService returned false.");
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to start recording: CameraControlService returned false.'
            ], 400);
        } catch (\Throwable $e) {
            Log::error("Exception when trying to start recording: " . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to start recording due to an exception.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function stop(
        CameraControlService $control,
        CameraFileService $files,
        VideoProcessingService $video
    ): JsonResponse {
        $control->stopRecording();
        sleep(5);

        $startTime = session('camera_start_time');
        $endTime = now();
        $duration = $startTime ? $startTime->diffInSeconds($endTime) : null;

        // Ambil playbackURI rekaman terbaru
        $playbackUri = $files->getLatestPlaybackUri();
        // dd($playbackUri);
        if (!$playbackUri) {
            Log::warning("No recording found after stopping camera.");
            return response()->json([
                'status' => 'error',
                'message' => 'No recording found'
            ], 404);
        }
        Log::info("Playback URI found: $playbackUri");

        // Download file menggunakan ISAPI download
        $relativePath = $files->downloadByPlaybackUri($playbackUri);
        // dd($relativePath);
        if (!$relativePath) {
            Log::error("Failed to download recording from playback URI.");
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to download recording'
            ], 500);
        }

        $fullPath = storage_path('app/public/' . $relativePath);

        // Generate thumbnail
        $thumb = 'thumbnails/thumb_' . pathinfo($relativePath, PATHINFO_FILENAME) . '.jpg';
        $thumbPath = storage_path('app/public/' . $thumb);

        try {
            $video->generateThumbnail($fullPath, $thumbPath);
        } catch (\Throwable $e) {
            Log::error("Thumbnail generation failed", ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Thumbnail generation failed',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'start_time' => $startTime,
            'end_time' => $endTime,
            'duration_seconds' => $duration,
            'video' => $relativePath,
            'thumbnail' => $thumb
        ]);
    }
}
