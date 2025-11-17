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
    public $timeout = 0;
    public $tries = 1;

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

            // Init as usual
            $recordedSearch->initialize($this->fieldId, $this->startTime, $this->endTime);

            // ======================================================
            // 1. DETECT RTSP PER CAMERA
            // ======================================================
            $data = $prepareData->prepare($this->fieldId);
            $host = $data['host'];
            $user = $data['user'];
            $pass = $data['pass'];
            $channels = $data['manualChannel'];

            $rtspDetection = $recordedSearch->DetectPlaybackRTSP($host, $user, $pass, $channels);

            $hasRTSP = false;
            foreach ($rtspDetection as $ch => $info) {
                if ($info['success']) {
                    $hasRTSP = true;

                    Log::channel('camera-job')->info("[RTSP OK] Direct RTSP download for channel {$ch}", [
                        'rtsp_url' => $info['rtsp_url']
                    ]);

                    // DISPATCH RTSP JOB
                    DownloadVideoJob::dispatch(
                        "camera_{$ch}",
                        [], // URIs not needed for RTSP
                        $this->fieldId,
                        $this->userId,
                        $this->videoName,
                        $host,
                        $user,
                        $pass,
                        $this->startTime,
                        $this->endTime,
                        $this->recordingId,
                        true,
                        $info['rtsp_url']
                    )->onQueue('camera-record-video-download');
                }
            }

            // If at least one RTSP OK → skip ISAPI
            if ($hasRTSP) {
                Log::channel('camera-job')->info("[JOB] RTSP available → ISAPI skipped");
                return;
            }

            // ======================================================
            // 2. NO RTSP → FALLBACK TO ISAPI
            // ======================================================
            Log::channel('camera-job')->warning("[RTSP FAIL] Fallback to ISAPI search");

            $uris = $recordedSearch->getAllPlaybackUris();

            if (empty($uris)) {
                Log::channel('camera-job')->warning("[JOB] No ISAPI playback URIs found", [
                    'start' => $this->startTime,
                    'end' => $this->endTime
                ]);
                return;
            }

            foreach ($uris as $cameraKey => $cameraUris) {
                $cameraInfo = $recordedSearch->getCameraConnection($cameraKey);

                Log::channel('camera-job')->info("[DEBUG] Dispatching ISAPI DownloadVideoJob", [
                    'camera_key' => $cameraKey,
                    'uri_count' => count($cameraUris),
                    'host' => $cameraInfo['host']
                ]);

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
                    $this->recordingId,
                    false,
                    null
                )->onQueue('camera-record-video-download');
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
