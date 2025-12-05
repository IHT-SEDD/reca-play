<?php

namespace App\Http\Controllers\Watch;

use App\Http\Controllers\Controller;
use App\Models\Record\RecordedVideo;
use App\Services\Support\ResponseHelperService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;

class WatchController extends Controller
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

    // ============================================================
    // Main view
    // ============================================================
    public function index($videoEncrypt)
    {
        return view('pages.watch.index', compact('videoEncrypt'));
    }

    // ============================================================
    // Video data
    // ============================================================
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

    // ============================================================
    // Like video
    // ============================================================
    public function likeVideo(Request $request)
    {
        $authUser = Auth::user();
        if (!$authUser) {
            return $this->responseHelperService->errorResponse(
                'You must be logged in to like videos!',
                401
            );
        }

        $video = RecordedVideo::with('recording.user')
            ->find($request->id);

        if (!$video) {
            return $this->responseHelperService->errorResponse(
                'Video not found!',
                404
            );
        }

        if ($video->recording->user->id === $authUser->id) {
            return $this->responseHelperService->errorResponse(
                'You cannot like your own video!',
                403
            );
        }

        DB::beginTransaction();
        try {
            $video->likes = ($video->likes ?? 0) + 1;
            $video->save();

            DB::commit();

            return $this->responseHelperService->successResponse(
                message: 'Video liked successfully!',
                data: ['likes' => $video->likes]
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->responseHelperService->errorResponse(
                'Failed to like video!',
                500,
                $e->getMessage()
            );
        }
    }

    // ============================================================
    // Dislike video
    // ============================================================
    public function dislikeVideo(Request $request)
    {
        $authUser = Auth::user();
        if (!$authUser) {
            return $this->responseHelperService->errorResponse(
                'You must be logged in to dislike videos!',
                401
            );
        }

        $video = RecordedVideo::with('recording.user')
            ->find($request->id);

        if (!$video) {
            return $this->responseHelperService->errorResponse(
                'Video not found!',
                404
            );
        }

        if ($video->recording->user->id === $authUser->id) {
            return $this->responseHelperService->errorResponse(
                'You cannot dislike your own video!',
                403
            );
        }

        DB::beginTransaction();
        try {
            $video->dislikes = ($video->dislikes ?? 0) + 1;
            $video->save();

            DB::commit();

            return $this->responseHelperService->successResponse(
                message: 'Video disliked successfully!',
                data: ['dislikes' => $video->dislikes]
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->responseHelperService->errorResponse(
                'Failed to dislike video!',
                500,
                $e->getMessage()
            );
        }
    }

    // ============================================================
    // Follow owner video
    // ============================================================
    public function followOwnerVideo(Request $request)
    {
        $authUser = Auth::user();
        if (!$authUser) {
            return $this->responseHelperService->errorResponse(
                'You must be logged in to follow users!',
                401
            );
        }

        $userToFollow = \App\Models\User::find($request->id);

        if (!$userToFollow) {
            return $this->responseHelperService->errorResponse(
                'User not found!',
                404
            );
        }

        if (!$userToFollow) {
            return $this->responseHelperService->errorResponse(
                'User not found!',
                404
            );
        }

        if ($userToFollow->id === $authUser->id) {
            return $this->responseHelperService->errorResponse(
                'You cannot follow yourself!',
                403
            );
        }

        DB::beginTransaction();
        try {
            $userToFollow->followers = ($userToFollow->followers ?? 0) + 1;
            $userToFollow->save();

            DB::commit();

            return $this->responseHelperService->successResponse(
                message: 'You followed this user!',
                data: ['followers' => $userToFollow->followers]
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->responseHelperService->errorResponse(
                'Failed to follow user!',
                500,
                $e->getMessage()
            );
        }
    }
}
