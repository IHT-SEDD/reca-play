<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Camera\CameraControlService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class CameraRecordController extends Controller
{
    protected CameraControlService $cameraControlService;

    public function __construct(CameraControlService $cameraControlService)
    {
        $this->cameraControlService = $cameraControlService;
    }

    public function start(): JsonResponse
    {
        try {
            if ($this->cameraControlService->startRecording()) {
                session(['camera_start_time' => now()]);

                return response()->json([
                    'status' => 'success',
                    'start_time' => now()->toDateTimeString()
                ]);
            }

            Log::channel('camera-record')->error("Failed to start recording: CameraControlService returned false.");

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to start recording: CameraControlService returned false.'
            ], 400);
        } catch (\Throwable $e) {
            Log::channel('camera-record')->error("Exception when trying to start recording: " . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to start recording due to an exception.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
