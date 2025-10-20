<?php

namespace App\Jobs\Camera;

use App\Services\Camera\RecordedSearchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ThumbnailVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $videoPath;
    protected string $thumbnailPath;

    public $tries = 1;
    public $timeout = 0;

    /**
     * Create a new job instance.
     */
    public function __construct(string $videoPath, string $thumbnailPath)
    {
        $this->videoPath = $videoPath;
        $this->thumbnailPath = $thumbnailPath;
    }

    /**
     * Execute the job.
     */
    public function handle(RecordedSearchService $recordedSearch)
    {
        Log::channel('camera-job')->info("[JOB] ThumbnailVideoJob started", [
            'videoPath' => $this->videoPath,
            'thumbnailPath' => $this->thumbnailPath
        ]);

        if (!file_exists($this->videoPath) || filesize($this->videoPath) === 0) {
            Log::channel('camera-job')->error("[THUMB FAIL] Video tidak valid", [
                'videoPath' => $this->videoPath
            ]);
            return;
        }

        try {
            $recordedSearch->generateThumbnail($this->videoPath, $this->thumbnailPath);

            Log::channel('camera-job')->info("[JOB] ThumbnailVideoJob finished", [
                'thumbnailPath' => $this->thumbnailPath,
                'size' => file_exists($this->thumbnailPath) ? filesize($this->thumbnailPath) : 0
            ]);
        } catch (\Throwable $e) {
            Log::channel('camera-job')->error("[THUMB FAIL]", [
                'error' => $e->getMessage(),
                'videoPath' => $this->videoPath,
                'thumbnailPath' => $this->thumbnailPath
            ]);
        }
    }
}
