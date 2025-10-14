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

    public $tries = 3;
    public $timeout = 120;

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
        if (!file_exists($this->videoPath)) return;

        Log::channel('camera-record')->info("[JOB] ThumbnailVideoJob started", [
            'video' => basename($this->videoPath)
        ]);

        $recordedSearch->generateThumbnail($this->videoPath, $this->thumbnailPath);

        Log::channel('camera-record')->info("[JOB] ThumbnailVideoJob finished", [
            'thumbnail' => basename($this->thumbnailPath)
        ]);
    }
}
