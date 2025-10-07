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
        return view('pages.recording.index');
    }

    public function getRecordings()
    {
        $recordings = Recording::select('*')
            ->with(['field.venue', 'recordedVideo', 'user'])
            ->where('user_id', Auth::id())
            ->get();

        return response()->json($recordings);
    }

    public function watchVideo($hashedId)
    {
        return view('pages.recording.watch.video', compact('hashedId'));
    }
}
