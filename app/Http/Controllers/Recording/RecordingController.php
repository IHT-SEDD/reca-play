<?php

namespace App\Http\Controllers\Recording;

use App\Http\Controllers\Controller;
use App\Models\Record\RecordedVideo;
use App\Models\Record\Recording;
use Carbon\Carbon;
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
        $userTimezone = Auth::user()->timezone ?? config('app.timezone');

        $startOfDay = Carbon::now($userTimezone)->startOfDay();
        $endOfDay = Carbon::now($userTimezone)->endOfDay();

        $recordings = Recording::with(['field.venue', 'recordedVideo', 'user'])
            ->where('user_id', Auth::id())
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->get();

        return response()->json($recordings);
    }
}
