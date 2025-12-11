<?php

namespace App\Jobs\Highlight;

use App\Services\Camera\RecordedSearchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ThumbnailHighlightVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ?int $recordingId;
    protected string $videoPath;
    protected string $thumbnailFile;

    public $tries = 1;
    public $timeout = 0;

    /**
     * Create a new job instance.
     */
    public function __construct(?int $recordingId, string $videoPath, string $thumbnailFile)
    {
        $this->recordingId = $recordingId;
        $this->videoPath = $videoPath;
        $this->thumbnailFile = $thumbnailFile;
    }

    /**
     * Execute the job.
     */
    public function handle(RecordedSearchService $recordedSearch)
    {
        Log::channel('highlight-job')->info("[HIGHLIGHT THUMB] ThumbnailVideoJob started", [
            'videoPath' => $this->videoPath
        ]);

        if (!file_exists($this->videoPath) || filesize($this->videoPath) === 0) {
            Log::channel('highlight-job')->error("[HIGHLIGHT THUMB FAIL] Video missing.", [
                'videoPath' => $this->videoPath
            ]);
            return;
        }

        try {
            $recordedSearch->generateThumbnail($this->videoPath, $this->thumbnailFile);

            Log::channel('highlight-job')->info("[HIGHLIGHT THUMB] Thumbnail generated", [
                'thumbnailPath' => $this->thumbnailFile,
                'size' => file_exists($this->thumbnailFile) ? filesize($this->thumbnailFile) : 0
            ]);

            $videoExists = \App\Models\Record\RecordedVideo::where('recording_id', $this->recordingId)
                ->where('video_filename', basename($this->videoPath))
                ->first();

            if (!$videoExists) {
                Log::channel('highlight-job')->info("[HIGHLIGHT THUMB] Video not found in DB, dispatch InsertRecordedVideoJob", [
                    'recordingId' => $this->recordingId,
                    'videoFile' => $this->videoPath,
                ]);

                InsertHighlightedVideoJob::dispatch(
                    $this->recordingId,
                    $this->videoPath,
                    basename($this->videoPath),
                    $this->thumbnailFile,
                    basename($this->thumbnailFile)
                )->onQueue('camera-highlight-video-insert');
            } else {
                Log::channel('highlight-job')->info("[HIGHLIGHT THUMB] Video exists in DB, update thumbnail", [
                    'recordingId' => $this->recordingId,
                    'videoFile' => $this->videoPath
                ]);

                $videoExists->update([
                    'thumbnail_path' => $this->thumbnailFile,
                    'thumbnail_filename' => basename($this->thumbnailFile),
                ]);
            }
        } catch (\Throwable $e) {
            Log::channel('highlight-job')->error("[HIGHLIGHT THUMB FAIL]", [
                'error' => $e->getMessage(),
                'videoPath' => $this->videoPath,
                'thumbnailPath' => $this->thumbnailFile
            ]);
        }
    }
}
