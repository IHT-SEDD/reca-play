<?php

namespace App\Jobs\Camera;

use App\Services\Camera\RecordedSearchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;

class DownloadVideoJob implements ShouldQueue, ShouldBeUniqueUntilProcessing
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $cameraKey;
    protected array $uris;
    protected int $fieldId;
    protected ?int $userId;
    protected string $videoName;
    protected string $host;
    protected string $user;
    protected string $pass;
    protected string $startTime;
    protected string $endTime;
    protected int $recordingId;

    public $tries = 3;
    public $timeout = 0;
    public $backoff = [60, 120, 300];

    public $uniqueFor = 1800;
    public function uniqueId(): string
    {
        return $this->cameraKey . '_' . $this->recordingId;
    }

    /**
     * Create a new job instance.
     */
    public function __construct(
        string $cameraKey,
        array $uris,
        int $fieldId,
        ?int $userId,
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
            Log::channel('camera-job')->info('[JOB] DownloadVideoJob started', [
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

            Log::channel('camera-job')->info('[DEBUG] DownloadVideoJob output file', [
                'file' => $file,
                'exists' => $file ? file_exists($file) : false,
                'size' => $file && file_exists($file) ? filesize($file) : 0
            ]);

            if ($file && file_exists($file) && filesize($file) > 0) {
                $flagPath = storage_path("app/tmp_recordings/{$this->cameraKey}_{$this->recordingId}_trim.lock");

                if (!file_exists($flagPath)) {
                    file_put_contents($flagPath, now()->toDateTimeString());
                    TrimVideoJob::dispatch(
                        $file,
                        $this->startTime,
                        $this->endTime,
                        $this->cameraKey,
                        $this->videoName,
                        $this->recordingId
                    )->onQueue('camera-record-video-trim');

                    Log::channel('camera-job')->info('[JOB] TrimVideoJob dispatched (first time)', [
                        'camera_key' => $this->cameraKey,
                        'flag' => basename($flagPath)
                    ]);
                } else {
                    Log::channel('camera-job')->warning('[JOB] TrimVideoJob skipped (already dispatched)', [
                        'camera_key' => $this->cameraKey,
                        'flag' => basename($flagPath)
                    ]);
                }
            }

            Log::channel('camera-job')->info('[JOB] DownloadVideoJob finished', [
                'camera_key' => $this->cameraKey,
                'recording_id' => $this->recordingId,
            ]);
            return;
        } catch (\Throwable $e) {
            Log::channel('camera-job')->error('[JOB ERROR] DownloadVideoJob failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
