<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Models\Record\RecordedVideo;
use App\Models\Record\Recording;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $userTimezone = Auth::user()->timezone ?? config('app.timezone');

        $fromDate = Carbon::now($userTimezone)->subDays(2)->startOfDay();
        $toDate = Carbon::now($userTimezone)->endOfDay();

        $videos = Recording::with(['field.venue', 'recordedVideo', 'user'])
            // ->whereBetween('created_at', [$fromDate, $toDate])
            ->latest('created_at')
            ->limit(10)
            ->get();

        return response()->json($videos);
    }
}
