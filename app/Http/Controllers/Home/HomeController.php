<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Models\Record\RecordedVideo;
use App\Models\Record\Recording;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Vinkla\Hashids\Facades\Hashids;

class HomeController extends Controller
{
    public function index()
    {
        return view('pages.home.index');
    }

    // ==== Get latest videos for homepage ==== //
    public function getVideos()
    {
        $videos = Recording::with(['field.venue', 'recordedVideo', 'user'])
            ->latest()
            ->take(10)
            ->get();

        return response()->json($videos);
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

        $encodedId = Hashids::connection('video')->encode($video->id);

        $shareUrl = URL::to('/video/watch/' . $encodedId);

        return response()->json([
            'success' => true,
            'url' => $shareUrl
        ]);
    }

    // ==== Watch shared video ==== //
    public function watchVideo($videoEncrypt)
    {
        try {
            $videoId = decrypt($videoEncrypt);
            $video = RecordedVideo::with('recording.field.venue', 'recording.user')->findOrFail($videoId);

            return view('pages.home.watch', compact('video'));
        } catch (\Exception $e) {
            abort(404, 'Invalid or expired video link.');
        }
    }
}
