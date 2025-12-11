<?php

namespace App\Jobs\Highlight;

use App\Services\Camera\RecordedSearchService;
use App\Services\Camera\PrepareDataService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GetPlaybackHighlightUrisJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $fieldId;
    protected ?int $userId;
    protected ?int $recordingId;
    protected ?string $videoName;
    protected string $highlightStart;
    protected string $highlightEnd;

    /**
     * Job timeout (seconds)
     */
    public $timeout = 0;
    public $tries = 1;

    /**
     * Create a new job instance.
     */
    public function __construct(
        int $fieldId,
        ?int $userId,
        ?int $recordingId,
        ?string $videoName,
        string $highlightStart,
        string $highlightEnd
    ) {
        $this->fieldId = $fieldId;
        $this->userId = $userId;
        $this->recordingId = $recordingId;
        $this->videoName = $videoName;
        $this->highlightStart = $highlightStart;
        $this->highlightEnd = $highlightEnd;
    }

    /**
     * Execute the job.
     */
    public function handle(RecordedSearchService $recordedSearch): void
    {
        try {
            Log::channel('highlight-job')->info("[HIGHLIGHT JOB] GetPlaybackHighlightUrisJob started", [
                'field_id' => $this->fieldId,
                'recording_id' => $this->recordingId,
                'highlight_start' => $this->highlightStart,
                'highlight_end' => $this->highlightEnd,
            ]);

            $recordedSearch->initialize($this->fieldId, $this->highlightStart, $this->highlightEnd);
            $uris = $recordedSearch->getAllPlaybackUris();

            if (empty($uris)) {
                Log::channel('highlight-job')->warning("[HIGHLIGHT JOB] No playback URIs found", [
                    'field_id' => $this->fieldId,
                    'user_id' => $this->userId ?? null,
                    'highlight_start' => $this->highlightStart,
                    'highlight_end' => $this->highlightEnd,
                ]);
                return;
            }

            foreach ($uris as $cameraKey => $cameraUris) {
                $cameraInfo = $recordedSearch->getCameraConnection($cameraKey);

                Log::channel('highlight-job')->info("[HIGHLIGHT DEBUG] Dispatching DownloadHighlightVideoJob", [
                    'camera_key' => $cameraKey,
                    'uri_count' => count($cameraUris),
                    'host' => $cameraInfo['host'],
                    'user_id' => $this->userId ?? null,
                ]);

                DownloadVideoHighlightJob::dispatch(
                    $cameraUris,
                    $this->fieldId,
                    $this->userId,
                    $this->recordingId,
                    $this->videoName,
                    $cameraKey,
                    $cameraInfo['host'],
                    $cameraInfo['user'],
                    $cameraInfo['pass'],
                    $this->highlightStart,
                    $this->highlightEnd,
                )->onQueue('camera-highlight-video-download');

                Log::channel('highlight-job')->info("[HIGHLIGHT JOB] DownloadHighlightVideoJob dispatched", [
                    'camera_key' => $cameraKey,
                    'uri_count' => count($cameraUris),
                ]);
            }

            Log::channel('highlight-job')->info("[HIGHLIGHT JOB] GetPlaybackHighlightUrisJob finished", [
                'recording_id' => $this->recordingId,
            ]);
        } catch (\Throwable $e) {
            Log::channel('highlight-job')->error("[HIGHLIGHT JOB ERROR] GetPlaybackHighlightUrisJob failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
