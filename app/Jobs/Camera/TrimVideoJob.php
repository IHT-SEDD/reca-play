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
use Symfony\Component\Process\Process;

class TrimVideoJob implements ShouldQueue, ShouldBeUniqueUntilProcessing
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

    public $uniqueFor = 900;

    public function uniqueId(): string
    {
        return $this->cameraKey . '_' . $this->recordingId;
    }

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
        // ==== VALIDASI FILE ====
        if (!file_exists($this->inputFile) || filesize($this->inputFile) < 1024) {
            Log::channel('camera-job')->warning("[TRIM FAIL] Input file not found", [
                'inputFile' => $this->inputFile
            ]);
            return;
        }

        // ==== STABILISASI FILE ====
        $prevSize = filesize($this->inputFile);
        $stable = false;
        $maxRetries = 12;
        $sleepSec = 5;

        for ($i = 0; $i < $maxRetries; $i++) {
            sleep($sleepSec);
            clearstatcache(true, $this->inputFile);
            $currentSize = filesize($this->inputFile);

            if ($currentSize === $prevSize) {
                $stable = true;
                break;
            }

            Log::channel('camera-job')->info("[TRIM WAIT] File is still growing, waiting for it to stabilize...", [
                'camera_key' => $this->cameraKey,
                'iteration' => $i + 1,
                'prev_size' => $prevSize,
                'current_size' => $currentSize
            ]);

            $prevSize = $currentSize;
        }

        if (! $stable) {
            Log::channel('camera-job')->warning("[TRIM SKIP] File not stable after 3 checks, skipping job.", [
                'camera_key' => $this->cameraKey,
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

            $startDT = new \DateTime($this->startTime);
            $endDT = new \DateTime($this->endTime);

            $firstSegmentStart = $recordedSearch->firstSegmentStart ?? 0;

            $startSec = max(0, $startDT->getTimestamp() - $firstSegmentStart);
            $duration = max(1, $endDT->getTimestamp() - $startDT->getTimestamp());

            try {
                $videoLength = (int) trim((new Process([
                    'ffprobe',
                    '-v',
                    'error',
                    '-show_entries',
                    'format=duration',
                    '-of',
                    'default=noprint_wrappers=1:nokey=1',
                    $this->inputFile
                ]))->mustRun()->getOutput());
            } catch (\Throwable $e) {
                Log::channel('camera-job')->error("[TRIM FAIL] ffprobe failed", [
                    'inputFile' => $this->inputFile,
                    'error' => $e->getMessage()
                ]);
                return;
            }

            if ($startSec >= $videoLength) {
                Log::channel('camera-job')->warning("[TRIM WARN] startSec exceeds video length", [
                    'startSec' => $startSec,
                    'videoLength' => $videoLength
                ]);
                return;
            }

            $duration = min($duration, $videoLength - $startSec);

            if ($duration <= 0) {
                Log::channel('camera-job')->warning("[TRIM WARN] Invalid duration, skipping trim", [
                    'duration' => $duration
                ]);
                return;
            }

            // $duration = $endDT->getTimestamp() - $startDT->getTimestamp();

            // Log::channel('camera-job')->info("[TRIM DEBUG] Calculated duration", [
            //     'start' => $this->startTime,
            //     'end' => $this->endTime,
            //     'duration' => $duration
            // ]);

            // Log::channel('camera-job')->info("[TRIM DEBUG] Calculated startSec & duration", [
            //     'startTime' => $this->startTime,
            //     'endTime' => $this->endTime,
            //     'firstSegmentStart' => $firstSegmentStart,
            //     'startSec' => $startSec,
            //     'duration' => $duration
            // ]);

            // if ($duration <= 0) {
            //     Log::channel('camera-job')->warning("[TRIM WARN] Invalid duration, skipping trim", [
            //         'start' => $this->startTime,
            //         'end' => $this->endTime
            //     ]);
            //     return;
            // }
            // if ($duration <= 0 || $startSec < 0) {
            //     Log::channel('camera-job')->warning("[TRIM WARN] Invalid duration or startSec, skipping trim", [
            //         'startSec' => $startSec,
            //         'duration' => $duration
            //     ]);
            //     return;
            // }

            $date = now()->format('dmy');
            $outputDir = storage_path('app/public/recordings');
            @mkdir($outputDir, 0777, true);

            $outputFile = "{$outputDir}/{$this->videoName}_{$this->cameraKey}_{$date}.mp4";

            // $success = $recordedSearch->trimVideo($this->inputFile, 0, $duration, $outputFile, false);
            $success = $recordedSearch->trimVideo($this->inputFile, $startSec, $duration, $outputFile, false);

            if (! $success || ! file_exists($outputFile) || filesize($outputFile) < 1024) {
                Log::channel('camera-job')->warning('[TRIM WARN] Fast trim failed, fallback to re-encode.', [
                    'camera_key' => $this->cameraKey,
                    'outputFile' => $outputFile
                ]);

                // $success = $recordedSearch->trimVideo($this->inputFile, 0, $duration, $outputFile, true);
                $success = $recordedSearch->trimVideo($this->inputFile, $startSec, $duration, $outputFile, true);
            }

            if (! $success || ! file_exists($outputFile) || filesize($outputFile) === 0) {
                Log::channel('camera-job')->warning('[TRIM WARN] Trim totally failed or the output is null', [
                    'camera_key' => $this->cameraKey,
                    'outputFile' => $outputFile
                ]);
                return;
            }

            Log::channel('camera-job')->info('[JOB] TrimVideoJob finished successfully', [
                'output' => basename($outputFile),
                'size' => filesize($outputFile)
            ]);

            WatermarkVideoJob::dispatch($outputFile)
                ->onQueue('camera-record-video-watermark');

            $thumbnailDir = storage_path('app/public/thumbnails');
            @mkdir($thumbnailDir, 0777, true);
            $thumbnailFile = "{$thumbnailDir}/{$this->videoName}_{$this->cameraKey}_{$date}_thumb.jpg";

            if (file_exists($outputFile) && filesize($outputFile) > 0) {
                ThumbnailVideoJob::dispatch($outputFile, $thumbnailFile)
                    ->onQueue('camera-record-video-thumb');
            } else {
                Log::channel('camera-job')->warning("[TRIM WARN] Invalid MP4 file, skipping thumbnail.", [
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

            Log::channel('camera-job')->info('[JOB] TrimVideoJob finished (completed)', [
                'camera_key' => $this->cameraKey,
                'recording_id' => $this->recordingId,
            ]);
            return;
        } catch (\Throwable $e) {
            Log::channel('camera-job')->error('[JOB ERROR] TrimVideoJob failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
