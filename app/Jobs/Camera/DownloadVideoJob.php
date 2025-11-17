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
    protected int $userId;
    protected string $videoName;
    protected string $host;
    protected string $user;
    protected string $pass;
    protected string $startTime;
    protected string $endTime;
    protected int $recordingId;
    protected bool $useRTSP;
    protected ?string $rtspUrl;

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
        int $userId,
        string $videoName,
        string $host,
        string $user,
        string $pass,
        string $startTime,
        string $endTime,
        int $recordingId,
        bool $useRTSP = false,
        ?string $rtspUrl = null
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
        $this->useRTSP = $useRTSP;
        $this->rtspUrl = $rtspUrl;
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
                'rtsp_mode' => $this->useRTSP
            ]);

            // ======================================================
            // 1. RTSP MODE
            // ======================================================
            if ($this->useRTSP && $this->rtspUrl) {

                $savePath = storage_path("app/public/recordings/{$this->videoName}_{$this->cameraKey}.mp4");

                $ok = $recordedSearch->DownloadVideoViaRTSP(
                    $this->rtspUrl,
                    $savePath,
                    $this->startTime,
                    $this->endTime
                );

                if ($ok) {
                    Log::channel('camera-job')->info('[JOB] RTSP download complete', [
                        'file' => $savePath,
                    ]);

                    TrimVideoJob::dispatch(
                        $savePath,
                        $this->startTime,
                        $this->endTime,
                        $this->cameraKey,
                        $this->videoName,
                        $this->recordingId
                    )->onQueue('camera-record-video-trim');

                    return;
                }

                Log::channel('camera-job')->warning("[JOB] RTSP download failed, fallback to ISAPI");
            }

            // ======================================================
            // 2. FALLBACK → ISAPI
            // ======================================================
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

            if ($file && file_exists($file)) {
                TrimVideoJob::dispatch(
                    $file,
                    $this->startTime,
                    $this->endTime,
                    $this->cameraKey,
                    $this->videoName,
                    $this->recordingId
                )->onQueue('camera-record-video-trim');
            }

            Log::channel('camera-job')->info('[JOB] DownloadVideoJob finished');
        } catch (\Throwable $e) {
            Log::channel('camera-job')->error('[JOB ERROR] DownloadVideoJob failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
