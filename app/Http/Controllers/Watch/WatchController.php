<?php

namespace App\Http\Controllers\Watch;

use App\Http\Controllers\Controller;
use App\Models\Record\RecordedVideo;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;

class WatchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($videoEncrypt)
    {
        return view('pages.watch.index', compact('videoEncrypt'));
    }

    public function watchData($videoEncrypt)
    {
        $videoId = Hashids::decode($videoEncrypt)[0] ?? null;

        if (!$videoId) {
            return response()->json(['error' => 'Video not found'], 404);
        }

        $video = RecordedVideo::with(['recording.user'])
            ->find($videoId);

        if (!$video) {
            return response()->json(['error' => 'Video not found'], 404);
        }

        return response()->json($video);
    }
}
