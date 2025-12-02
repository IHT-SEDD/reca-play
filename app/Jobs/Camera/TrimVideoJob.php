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
        if (!file_exists($this->inputFile)) {
            Log::channel('camera-job')->warning("[TRIM FAIL] Input file not found", [
                'inputFile' => $this->inputFile
            ]);
            return;
        }

        $prevSize = filesize($this->inputFile);
        $stable = false;

        for ($i = 0; $i < 3; $i++) {
            sleep(5);
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

            $outputFile = "{$outputDir}/{$this->videoName}_{$this->cameraKey}_{$date}.mp4";

            $success = $recordedSearch->trimVideo($this->inputFile, 0, $duration, $outputFile, false);

            if (! $success || ! file_exists($outputFile) || filesize($outputFile) < 1024) {
                Log::channel('camera-job')->warning('[TRIM WARN] Fast trim failed, fallback to re-encode.', [
                    'camera_key' => $this->cameraKey,
                    'outputFile' => $outputFile
                ]);

                $success = $recordedSearch->trimVideo($this->inputFile, 0, $duration, $outputFile, true);
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

            try {
                $watermarkFile = public_path('assets/img/logos/reca-white.png');

                Log::channel('camera-job')->info('[WM] Checking watermark file', [
                    'file' => $watermarkFile,
                    'exists' => file_exists($watermarkFile)
                ]);

                if (file_exists($watermarkFile)) {
                    $wmOutput = str_replace('.mp4', '_wm.mp4', $outputFile);

                    if ($wmOutput === $outputFile) {
                        $wmOutput = $outputFile . "_wm.mp4";
                    }

                    Log::channel('camera-job')->info('[WM] Applying watermark...', [
                        'source' => $outputFile,
                        'target' => $wmOutput
                    ]);

                    $process = new \Symfony\Component\Process\Process([
                        'ffmpeg',
                        '-y',
                        '-i',
                        $outputFile,
                        '-i',
                        $watermarkFile,
                        '-filter_complex',
                        "[1]scale=120:-1[wm];[0][wm]overlay=W-w-20:H-h-20:format=auto:alpha=0.35",
                        '-c:v',
                        'libx264',
                        '-preset',
                        'ultrafast',
                        '-crf',
                        '23',
                        '-c:a',
                        'copy',
                        $wmOutput
                    ]);

                    $process->setTimeout(0);
                    $process->run();

                    if ($process->isSuccessful() && file_exists($wmOutput) && filesize($wmOutput) > 0) {

                        Log::channel('camera-job')->info('[WM] Watermark success, replacing original');

                        @unlink($outputFile);
                        rename($wmOutput, $outputFile);
                    } else {
                        Log::channel('camera-job')->warning('[WM] FAILED: using original file', [
                            'error' => $process->getErrorOutput()
                        ]);
                    }
                } else {
                    Log::channel('camera-job')->warning('[WM] Watermark file missing, skipped.');
                }
            } catch (\Throwable $e) {
                Log::channel('camera-job')->error('[WM ERROR] Watermark error', [
                    'error' => $e->getMessage()
                ]);
            }

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
