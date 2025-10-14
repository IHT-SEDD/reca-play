<?php

namespace App\Jobs\Camera;

use App\Services\Camera\RecordedSearchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DownloadVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $cameraKey;
    protected array $uris;
    protected int $fieldId;
    protected int $userId;
    protected string $videoName;
    protected string $host;
    protected string $user;
    protected string $pass;
    protected string $startTime;
    protected string $endTime;
    protected int $recordingId;

    // public ?string $queue = 'camera-record-video-download';
    public $tries = 3;
    public $timeout = 0;
    public $backoff = [60, 180, 300];

    /**
     * Create a new job instance.
     */
    public function __construct(
        string $cameraKey,
        array $uris,
        int $fieldId,
        int $userId,
        string $videoName,
        string $host,
        string $user,
        string $pass,
        string $startTime,
        string $endTime,
        int $recordingId
    ) {
        $this->cameraKey = $cameraKey;
        $this->uris = $uris;
        $this->fieldId = $fieldId;
        $this->userId = $userId;
        $this->videoName = $videoName;
        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->recordingId = $recordingId;
    }

    /**
     * Execute the job.
     */
    public function handle(RecordedSearchService $recordedSearch): void
    {
        try {
            Log::channel('camera-record')->info('[JOB] DownloadVideoJob started', [
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
                $this->startTime,
                $this->endTime
            );
            

            if ($file && file_exists($file) && filesize($file) > 0) {
                TrimVideoJob::dispatch(
                    $file,
                    $this->startTime,
                    $this->endTime,
                    $this->cameraKey,
                    $this->videoName,
                    $this->recordingId
                )->onQueue('camera-record-video-trim');
            } else {
                Log::channel('camera-record')->warning('[JOB] DownloadVideoJob produced no valid file', [
                    'camera_key' => $this->cameraKey,
                ]);
            }
        } catch (\Throwable $e) {
            Log::channel('camera-record')->error('[JOB ERROR] DownloadVideoJob failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
