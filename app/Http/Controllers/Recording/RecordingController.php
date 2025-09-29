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
        $recordings = Recording::with(['field.venue', 'recordedVideo'])
            ->where('user_id', Auth::id())
            ->get();

        return response()->json($recordings);
    }
}
