<?php

namespace App\Jobs\Camera;


use App\Services\Camera\RecordedSearchService;
use App\Services\Camera\PrepareDataService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GetPlaybackUrisJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $fieldId;
    protected int $userId;
    protected string $videoName;
    protected string $startTime;
    protected string $endTime;
    protected int $recordingId;

    /**
     * Job timeout (seconds)
     */
    public $timeout = 600; // 10 minutes
    public $tries = 3;
    public $queue = 'camera-record-video-search';

    /**
     * Create a new job instance.
     */
    public function __construct(
        int $fieldId,
        string $startTime,
        string $endTime,
        int $userId,
        string $videoName,
        int $recordingId
    ) {
        $this->fieldId = $fieldId;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->userId = $userId;
        $this->videoName = $videoName;
        $this->recordingId = $recordingId;
    }

    /**
     * Execute the job.
     */
    public function handle(RecordedSearchService $recordedSearch, PrepareDataService $prepareData): void
    {
        try {
            Log::channel('camera-job')->info("[JOB] GetPlaybackUrisJob started", [
                'recording_id' => $this->recordingId,
                'field_id' => $this->fieldId,
            ]);

            $recordedSearch->initialize($this->fieldId, $this->startTime, $this->endTime);
            $uris = $recordedSearch->getAllPlaybackUris();

            if (empty($uris)) {
                Log::channel('camera-job')->warning("[JOB] No playback URIs found", [
                    'recording_id' => $this->recordingId,
                ]);
                return;
            }

            foreach ($uris as $cameraKey => $cameraUris) {
                $cameraInfo = $recordedSearch->getCameraConnection($cameraKey);

                DownloadVideoJob::dispatch(
                    $cameraKey,
                    $cameraUris,
                    $this->fieldId,
                    $this->userId,
                    $this->videoName,
                    $cameraInfo['host'],
                    $cameraInfo['user'],
                    $cameraInfo['pass'],
                    $this->startTime,
                    $this->endTime,
                    $this->recordingId
                )->onQueue('camera-record-video-download');

                Log::channel('camera-job')->info("[JOB] DownloadVideoJob dispatched", [
                    'camera_key' => $cameraKey,
                    'uri_count' => count($cameraUris),
                ]);
            }

            Log::channel('camera-job')->info("[JOB] GetPlaybackUrisJob finished", [
                'recording_id' => $this->recordingId,
            ]);
        } catch (\Throwable $e) {
            Log::channel('camera-job')->error("[JOB ERROR] GetPlaybackUrisJob failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
