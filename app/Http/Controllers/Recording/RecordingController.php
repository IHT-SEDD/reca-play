<?php

namespace App\Http\Controllers\Recording;

use App\Http\Controllers\Controller;
use App\Models\Record\RecordedVideo;
use App\Models\Record\Recording;
use App\Models\Session\SessionCode;
use App\Services\Support\ResponseHelperService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Iman\Streamer\VideoStreamer;

class RecordingController extends Controller
{
    // ============================================================
    // Init service
    // ============================================================
    protected ResponseHelperService $responseHelperService;

    public function __construct(
        ResponseHelperService $responseHelperService,
    ) {
        $this->responseHelperService = $responseHelperService;
    }

    public function index()
    {
        return view('pages.recording.index');
    }

    public function getRecordings()
    {
        $userTimezone = Auth::user()->timezone ?? config('app.timezone');

        $startOfDay = Carbon::now($userTimezone)->subDays(5)->startOfDay();
        $endOfDay = Carbon::now($userTimezone)->endOfDay();

        $recordings = Recording::with(['field.venue', 'recordedVideo', 'user'])
            ->where('user_id', Auth::id())
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($recordings);
    }

    public function getVideos(Request $request)
    {
        $user = Auth::user();
        $accessCode = $request->access_code;

        try {
            $accessCode = $request->access_code;

            if (!$accessCode) {
                return $this->responseHelperService->errorResponse(
                    'Access code is required.',
                    422
                );
            }

            $sessionCode = SessionCode::where('generated_code', $accessCode)
                ->where('status', 'done')
                ->first();

            if (!$sessionCode) {
                return $this->responseHelperService->errorResponse(
                    'Session code not found or invalid.',
                    404
                );
            }

            if ($user && $sessionCode->user_id !== $user->id) {
                $sessionCode->user_id = $user->id;
                $sessionCode->save();
            }

            $recording = Recording::where('id', $sessionCode->recording_id)
                ->where('status', 'done')
                ->first();

            if (!$recording) {
                return $this->responseHelperService->errorResponse(
                    'Recording not found or not finished yet.',
                    404
                );
            }

            if ($user && $recording->user_id !== $user->id) {
                $recording->user_id = $user->id;
                $recording->save();
            }

            return $this->responseHelperService->successResponse(
                'Videos retrieved successfully.',
                [
                    'recording' => $recording,
                    'session_code' => $sessionCode,
                ]
            );
        } catch (\Throwable $th) {
            Log::error('Error getVideos', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);

            return $this->responseHelperService->errorResponse(
                'Something went wrong. Please try again later.',
                500
            );
        }
    }
}
