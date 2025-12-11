<?php

namespace App\Jobs\Highlight;

use App\Services\Camera\RecordedSearchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;

class DownloadVideoHighlightJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $uris;
    protected int $fieldId;
    protected ?int $userId;
    protected ?int $recordingId;
    protected ?string $videoName;
    protected string $cameraKey;
    protected string $host;
    protected string $user;
    protected string $pass;
    protected string $highlightStart;
    protected string $highlightEnd;

    public $tries = 3;
    public $timeout = 0;
    public $backoff = [60, 120, 300];

    public $uniqueFor = 1800;
    public function uniqueId(): string
    {
        return $this->cameraKey . '_higlight_' . $this->recordingId;
    }

    /**
     * Create a new job instance.
     */
    public function __construct(
        array $uris,
        int $fieldId,
        ?int $userId,
        ?int $recordingId,
        ?string $videoName,
        string $cameraKey,
        string $host,
        string $user,
        string $pass,
        string $highlightStart,
        string $highlightEnd,
    ) {
        $this->uris = $uris;
        $this->fieldId = $fieldId;
        $this->userId = $userId;
        $this->recordingId = $recordingId;
        $this->videoName = $videoName;
        $this->cameraKey = $cameraKey;
        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        $this->highlightStart = $highlightStart;
        $this->highlightEnd = $highlightEnd;
    }

    /**
     * Execute the job.
     */
    public function handle(RecordedSearchService $recordedSearch): void
    {
        try {
            Log::channel('highlight-job')->info('[HIGHLIGHT JOB] DownloadVideoHighlightJob started', [
                'recording_id' => $this->recordingId,
                'camera_key' => $this->cameraKey,
            ]);

            $file = $recordedSearch->downloadByPlaybackUris(
                [$this->cameraKey => $this->uris],
                $this->fieldId,
                $this->userId,
                $this->videoName,
                $this->host,
                $this->user,
                $this->pass,
                $this->highlightStart,
                $this->highlightEnd
            );

            Log::channel('highlight-job')->info('[HIGHLIGHT DEBUG] DownloadVideoHighlightJob output file', [
                'file' => $file,
                'exists' => $file ? file_exists($file) : false,
                'size' => $file && file_exists($file) ? filesize($file) : 0
            ]);

            if ($file && file_exists($file) && filesize($file) > 0) {
                $flagPath = storage_path("app/tmp_highlights/{$this->cameraKey}_{$this->recordingId}_trim.lock");

                if (!file_exists($flagPath)) {
                    file_put_contents($flagPath, now()->toDateTimeString());
                    TrimHighlightVideoJob::dispatch(
                        $this->recordingId,
                        $this->videoName,
                        $file,
                        $this->highlightStart,
                        $this->highlightEnd,
                        $this->cameraKey,
                    )->onQueue('camera-highlight-video-trim');

                    Log::channel('highlight-job')->info('[HIGHLIGHT JOB] TrimHighlightVideoJob dispatched (first time)', [
                        'camera_key' => $this->cameraKey,
                        'flag' => basename($flagPath)
                    ]);
                } else {
                    Log::channel('highlight-job')->warning('[HIGHLIGHT JOB] TrimHighlightVideoJob skipped (already dispatched)', [
                        'camera_key' => $this->cameraKey,
                        'flag' => basename($flagPath)
                    ]);
                }
            }

            Log::channel('highlight-job')->info('[HIGHLIGHT JOB] DownloadVideoHighlightJob finished', [
                'camera_key' => $this->cameraKey,
                'recording_id' => $this->recordingId,
            ]);
            return;
        } catch (\Throwable $e) {
            Log::channel('highlight-job')->error('[HIGHLIGHT JOB ERROR] DownloadVideoHighlightJob failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
