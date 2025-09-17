<?php

namespace App\Http\Controllers\Recording;

use App\Http\Controllers\Controller;
use App\Models\Record\RecordedVideo;
use App\Models\Record\Recording;
use Illuminate\Support\Facades\Auth;
use Iman\Streamer\VideoStreamer;

class RecordingController extends Controller
{
    public function index()
    {
        $recordings = $this->getUserRecordings();
        return view('pages.recording.index', compact('recordings'));
    }

    protected function getUserRecordings()
    {
        return Recording::with(['field', 'recordedVideo'])
            ->where('user_id', Auth::user()->id)
            ->latest()
            ->paginate(20);
    }

    public function show(RecordedVideo $video)
    {
        $path = storage_path('app/public/' . $video->video_path);

        if (! file_exists($path)) {
            abort(404, 'Video not found.');
        }
    }
}
