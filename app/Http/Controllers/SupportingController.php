<?php

namespace App\Http\Controllers;

use App\Models\Record\RecordedVideo;
use App\Services\Support\SelectOptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class SupportingController extends Controller
{
    protected $selectOptionService;

    public function __construct(SelectOptionService $selectOptionService)
    {
        $this->selectOptionService = $selectOptionService;
    }

    // ==== Options service for tom-select ==== //
    public function selectOptions($option, Request $request)
    {
        $with = $request->get('with') ? explode(',', $request->get('with')) : [];

        $results = $this->selectOptionService->getOptions($option, $request->get('q'), $with);
        return response()->json($results);
    }

    // ==== Generate share link ==== //
    public function shareVideo($videoId)
    {
        $video = RecordedVideo::find($videoId);

        if (! $video) {
            return response()->json([
                'success' => false,
                'message' => 'Video not found.'
            ], 404);
        }

        $shareUrl = URL::to('/video/watch/' . $video->hashed_id);

        return response()->json([
            'success' => true,
            'url' => $shareUrl
        ]);
    }

    // ==== Download video ==== //
    public function downloadVideo($videoId)
    {
        $video = RecordedVideo::find($videoId);

        if (! $video) {
            return response()->json([
                'success' => false,
                'message' => 'Video not found.'
            ], 404);
        }

        $filePath = $video->video_path;

        if (! Storage::disk('public')->exists($filePath)) {
            return response()->json([
                'success' => false,
                'message' => 'Video file not found on server.'
            ], 404);
        }

        $publicUrl = asset('storage/' . $filePath);

        return response()->json([
            'success' => true,
            'url' => $publicUrl,
        ]);
    }

    // ==== Write JS log to File ==== //
    public function scanQrJsLogStore(Request $request)
    {
        $folder = 'logs/js-logs';
        $fileName = 'scan-qr-log.json';
        $path = storage_path("{$folder}/{$fileName}");

        if (!file_exists(storage_path($folder))) {
            mkdir(storage_path($folder), 0777, true);
        }

        $logEntry = [
            'timestamp' => now()->toDateTimeString(),
            'message' => $request->message,
            'data' => json_decode($request->data, true),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];

        if (!file_exists($path)) {
            file_put_contents($path, json_encode([$logEntry], JSON_PRETTY_PRINT));
        } else {
            $existing = json_decode(file_get_contents($path), true);
            $existing[] = $logEntry;
            file_put_contents($path, json_encode($existing, JSON_PRETTY_PRINT));
        }

        return response()->json(['status' => 'logged']);
    }
}
