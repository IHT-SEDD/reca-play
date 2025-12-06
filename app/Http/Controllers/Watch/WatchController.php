<?php

namespace App\Http\Controllers\Watch;

use App\Http\Controllers\Controller;
use App\Models\Record\RecordedVideo;
use App\Models\User\UserFollow;
use App\Models\Video\VideoUserLike;
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
            $existing = VideoUserLike::where('user_id', $authUser->id)
                ->where('recorded_video_id', $video->id)
                ->first();

            if ($existing && $existing->type === 'like') {
                $existing->delete();
                $video->decrement('likes');

                DB::commit();
                return $this->responseHelperService->successResponse(
                    'Like removed!',
                    ['likes' => $video->likes, 'status_like_dislike' => 'none']
                );
            }

            if ($existing && $existing->type === 'dislike') {
                $existing->update(['type' => 'like']);
                $video->increment('likes');
                $video->decrement('dislikes');

                DB::commit();
                return $this->responseHelperService->successResponse(
                    'Switched to like!',
                    ['likes' => $video->likes, 'dislikes' => $video->dislikes, 'status_like_dislike' => 'like']
                );
            }

            VideoUserLike::create([
                'user_id' => $authUser->id,
                'recorded_video_id' => $video->id,
                'type' => 'like'
            ]);

            $video->increment('likes');

            DB::commit();

            return $this->responseHelperService->successResponse(
                'Video liked!',
                ['likes' => $video->likes, 'status_like_dislike' => 'like']
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
            $existing = VideoUserLike::where('user_id', $authUser->id)
                ->where('recorded_video_id', $video->id)
                ->first();

            if ($existing && $existing->type === 'dislike') {
                $existing->delete();
                $video->decrement('dislikes');

                DB::commit();
                return $this->responseHelperService->successResponse(
                    'Dislike removed!',
                    ['dislikes' => $video->dislikes, 'status_like_dislike' => 'none']
                );
            }

            if ($existing && $existing->type === 'like') {
                $existing->update(['type' => 'dislike']);
                $video->increment('dislikes');
                $video->decrement('likes');

                DB::commit();
                return $this->responseHelperService->successResponse(
                    'Switched to dislike!',
                    ['likes' => $video->likes, 'dislikes' => $video->dislikes, 'status_like_dislike' => 'dislike']
                );
            }

            VideoUserLike::create([
                'user_id' => $authUser->id,
                'recorded_video_id' => $video->id,
                'type' => 'dislike'
            ]);

            $video->increment('dislikes');

            DB::commit();

            return $this->responseHelperService->successResponse(
                'Video disliked!',
                ['dislikes' => $video->dislikes, 'status_like_dislike' => 'dislike']
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

        if ($userToFollow->id === $authUser->id) {
            return $this->responseHelperService->errorResponse(
                'You cannot follow yourself!',
                403
            );
        }

        DB::beginTransaction();
        try {
            $existing = UserFollow::where('follower_id', $authUser->id)
                ->where('following_id', $userToFollow->id)
                ->first();

            if ($existing) {
                $existing->delete();
                $userToFollow->decrement('followers');

                DB::commit();
                return $this->responseHelperService->successResponse(
                    'Unfollowed!',
                    ['followers' => $userToFollow->followers, 'status_follow' => 'unfollow']
                );
            }

            UserFollow::create([
                'follower_id' => $authUser->id,
                'following_id' => $userToFollow->id
            ]);

            $userToFollow->increment('followers');

            DB::commit();

            return $this->responseHelperService->successResponse(
                'Followed!',
                ['followers' => $userToFollow->followers, 'status_follow' => 'follow']
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
