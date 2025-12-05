<?php

namespace App\Jobs;

use App\Mail\RecordedVideoReadyMail;
use App\Models\Record\RecordedVideo;
use App\Models\Record\Recording;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendRecordedVideoEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    protected int $recordedVideoId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $recordedVideoId)
    {
        $this->recordedVideoId = $recordedVideoId;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $video = RecordedVideo::find($this->recordedVideoId);

        if (!$video) {
            Log::channel('camera-job')->warning("[MAIL] RecordedVideo not found, skipping", [
                'recorded_video_id' => $this->recordedVideoId
            ]);
            return;
        }

        $recording = $video->recording;
        if (!$recording) {
            Log::channel('camera-job')->warning("[MAIL] Recording not found for recorded video, skipping", [
                'recorded_video_id' => $this->recordedVideoId
            ]);
            return;
        }

        $user = $recording->user;
        if (!$user || !$user->email) {
            Log::channel('camera-job')->info("[MAIL] No user/email attached to recording, skipping", [
                'recorded_video_id' => $this->recordedVideoId
            ]);
            return;
        }

        try {
            Mail::to($user->email)->send(new RecordedVideoReadyMail($video));

            Log::channel('camera-job')->info("[MAIL] Recorded video email sent", [
                'email' => $user->email,
                'recorded_video_id' => $video->id
            ]);
        } catch (\Throwable $e) {
            $attempts = method_exists($this, 'attempts') ? $this->attempts() : null;

            Log::channel('camera-job')->warning("[MAIL ERROR] Failed to send email", [
                'recorded_video_id' => $this->recordedVideoId,
                'email' => $user->email,
                'error' => $e->getMessage(),
                'attempts' => $attempts,
                'max_tries' => $this->tries
            ]);

            if ($attempts === null || $attempts < $this->tries) {
                throw $e;
            }

            Log::channel('camera-job')->info("[MAIL] reached max attempts, giving up (optional)", [
                'recorded_video_id' => $this->recordedVideoId,
                'email' => $user->email
            ]);
            return;
        }
    }
}
