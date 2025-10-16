<?php

namespace App\Jobs\Camera;

use App\Services\Camera\RecordedSearchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TrimVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $inputFile;
    protected string $startTime;
    protected string $endTime;
    protected string $cameraKey;
    protected string $videoName;
    protected int $recordingId;

    public $tries = 2;
    public $timeout = 0;
    public $backoff = [60, 120];

    /**
     * Create a new job instance.
     */
    public function __construct(
        string $inputFile,
        string $startTime,
        string $endTime,
        string $cameraKey,
        string $videoName,
        int $recordingId
    ) {
        $this->inputFile = $inputFile;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->cameraKey = $cameraKey;
        $this->videoName = $videoName;
        $this->recordingId = $recordingId;
    }

    /**
     * Execute the job.
     */
    public function handle(RecordedSearchService $recordedSearch): void
    {
        if (!file_exists($this->inputFile)) {
            Log::channel('camera-job')->warning("[TRIM FAIL] Input file tidak ditemukan", [
                'inputFile' => $this->inputFile
            ]);
            return;
        }

        try {
            Log::channel('camera-job')->info("[JOB] TrimVideoJob started", [
                'camera_key' => $this->cameraKey,
                'recording_id' => $this->recordingId,
                'inputFileSize' => filesize($this->inputFile)
            ]);

            $startDT  = new \DateTime($this->startTime);
            $endDT    = new \DateTime($this->endTime);
            $duration = $endDT->getTimestamp() - $startDT->getTimestamp();

            Log::channel('camera-job')->info("[TRIM DEBUG] Calculated duration", [
                'start' => $this->startTime,
                'end' => $this->endTime,
                'duration' => $duration
            ]);

            if ($duration <= 0) {
                Log::channel('camera-job')->warning("[TRIM WARN] Invalid duration, skipping trim", [
                    'start' => $this->startTime,
                    'end' => $this->endTime
                ]);
                return;
            }

            $date = now()->format('dmy');
            $outputDir = storage_path('app/public/recordings');
            @mkdir($outputDir, 0777, true);

            // $safeName   = preg_replace('/[^A-Za-z0-9_\-]/', '_', $this->videoName);
            $outputFile = "{$outputDir}/{$this->videoName}_{$this->cameraKey}_{$date}.mp4";

            $success = $recordedSearch->trimVideo($this->inputFile, 0, $duration, $outputFile, true);
            if (! $success || ! file_exists($outputFile) || filesize($outputFile) === 0) {
                Log::channel('camera-job')->warning('[TRIM WARN] Trim gagal atau output kosong', [
                    'camera_key' => $this->cameraKey,
                    'outputFile' => $outputFile
                ]);
                return;
            }

            Log::channel('camera-job')->info('[JOB] TrimVideoJob finished successfully', [
                'output' => basename($outputFile),
                'size' => filesize($outputFile)
            ]);

            $thumbnailDir = storage_path('app/public/thumbnails');
            @mkdir($thumbnailDir, 0777, true);
            $thumbnailFile = "{$thumbnailDir}/{$this->videoName}_{$this->cameraKey}_{$date}_thumb.jpg";

            if (file_exists($outputFile) && filesize($outputFile) > 0) {
                ThumbnailVideoJob::dispatch($outputFile, $thumbnailFile)
                    ->onQueue('camera-record-video-thumb');
            } else {
                Log::channel('camera-job')->warning("[TRIM WARN] File mp4 tidak valid, skip thumbnail", [
                    'file' => $outputFile
                ]);
            }

            if (!\App\Models\Record\RecordedVideo::where('recording_id', $this->recordingId)
                ->where('video_filename', basename($outputFile))
                ->exists()) {

                InsertRecordedVideoJob::dispatch(
                    (int) $this->recordingId,
                    $outputFile,
                    basename($outputFile),
                    $thumbnailFile,
                    basename($thumbnailFile)
                )->onQueue('camera-record-video-insert');
            }
        } catch (\Throwable $e) {
            Log::channel('camera-job')->error('[JOB ERROR] TrimVideoJob failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
