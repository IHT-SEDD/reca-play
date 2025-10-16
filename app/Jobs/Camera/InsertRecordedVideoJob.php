<?php

namespace App\Jobs\Camera;

use App\Models\Record\RecordedVideo;
use App\Models\Record\Recording;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class InsertRecordedVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $recordingId;
    protected string $videoPath;
    protected string $videoFilename;
    protected string $thumbnailPath;
    protected string $thumbnailFilename;

    public $tries = 3;
    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        int $recordingId,
        string $videoPath,
        string $videoFilename,
        string $thumbnailPath,
        string $thumbnailFilename
    ) {
        $this->recordingId = $recordingId;
        $this->videoPath = $videoPath;
        $this->videoFilename = $videoFilename;
        $this->thumbnailPath = $thumbnailPath;
        $this->thumbnailFilename = $thumbnailFilename;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        Log::channel('camera-record')->info("[JOB] InsertRecordedVideoJob started", [
            'recording_id' => $this->recordingId,
            'videoPath' => $this->videoPath,
            'thumbnailPath' => $this->thumbnailPath
        ]);

        if (!file_exists($this->videoPath)) {
            Log::channel('camera-record')->warning("[INSERT FAIL] Video tidak ditemukan", [
                'videoPath' => $this->videoPath
            ]);
            return;
        }

        RecordedVideo::create([
            'recording_id' => $this->recordingId,
            'video_path' => str_replace(storage_path('app/public/'), '', $this->videoPath),
            'video_filename' => $this->videoFilename,
            'thumbnail_path' => str_replace(storage_path('app/public/'), '', $this->thumbnailPath),
            'thumbnail_filename' => $this->thumbnailFilename,
            'video_size' => filesize($this->videoPath),
        ]);

        $recording = Recording::find($this->recordingId);
        if ($recording && $recording->status !== 'done') {
            $recording->update(['status' => 'done']);
            Log::channel('camera-record')->info("[JOB] Recording marked as done", [
                'recording_id' => $this->recordingId,
                'status' => 'done'
            ]);
        }

        Log::channel('camera-record')->info("[JOB] InsertRecordedVideoJob finished", [
            'recording_id' => $this->recordingId,
            'video_filename' => $this->videoFilename
        ]);
    }
}
