<?php

namespace App\Jobs\Highlight;

use App\Jobs\Camera\WatermarkVideoJob;
use App\Services\Camera\RecordedSearchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Symfony\Component\Process\Process;

class TrimHighlightVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ?int $recordingId;
    protected string $videoName;
    protected string $inputFile;
    protected string $highlightStart;
    protected string $highlightEnd;
    protected string $cameraKey;

    public $tries = 2;
    public $timeout = 0;
    public $backoff = [60, 120];

    public $uniqueFor = 900;
    public function uniqueId(): string
    {
        return $this->cameraKey . '_higlight_' . $this->recordingId;
    }

    /**
     * Create a new job instance.
     */
    public function __construct(
        ?int $recordingId,
        string $videoName,
        string $inputFile,
        string $highlightStart,
        string $highlightEnd,
        string $cameraKey,
    ) {
        $this->recordingId = $recordingId;
        $this->videoName = $videoName;
        $this->inputFile = $inputFile;
        $this->highlightStart = $highlightStart;
        $this->highlightEnd = $highlightEnd;
        $this->cameraKey = $cameraKey;
    }

    /**
     * Execute the job.
     */
    public function handle(RecordedSearchService $recordedSearch): void
    {
        if (!file_exists($this->inputFile)) {
            Log::channel('highlight-job')->warning("[HIGHLIGHT TRIM FAIL] Input file not found", [
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

            Log::channel('highlight-job')->info("[HIGHLIGHT TRIM WAIT] File is still growing, waiting for it to stabilize...", [
                'camera_key' => $this->cameraKey,
                'iteration' => $i + 1,
                'prev_size' => $prevSize,
                'current_size' => $currentSize
            ]);

            $prevSize = $currentSize;
        }

        if (! $stable) {
            Log::channel('highlight-job')->warning("[HIGHLIGHT TRIM SKIP] File not stable after 3 checks, skipping job.", [
                'camera_key' => $this->cameraKey,
                'inputFile' => $this->inputFile
            ]);
            return;
        }

        try {
            Log::channel('highlight-job')->info("[HIGHLIGHT JOB] TrimVideoJob started", [
                'camera_key' => $this->cameraKey,
                'recording_id' => $this->recordingId,
                'inputFileSize' => filesize($this->inputFile)
            ]);

            $startDT = new \DateTime($this->highlightStart);
            $endDT = new \DateTime($this->highlightEnd);
            $duration = $endDT->getTimestamp() - $startDT->getTimestamp();

            Log::channel('highlight-job')->info("[HIGHLIGHT TRIM DEBUG] Calculated duration", [
                'start' => $this->highlightStart,
                'end' => $this->highlightEnd,
                'duration' => $duration
            ]);

            if ($duration <= 0) {
                Log::channel('highlight-job')->warning("[HIGHLIGHT TRIM WARN] Invalid duration, skipping trim", [
                    'start' => $this->highlightStart,
                    'end' => $this->highlightEnd
                ]);
                return;
            }

            $ffprobe = new Process([
                'ffprobe',
                '-v',
                'error',
                '-show_entries',
                'format=duration',
                '-of',
                'default=noprint_wrappers=1:nokey=1',
                $this->inputFile
            ]);
            $ffprobe->setTimeout(0)->run();

            $receivedDuration = null;
            if ($ffprobe->isSuccessful()) {
                $receivedDuration = floatval(trim($ffprobe->getOutput()));
            } else {
                Log::channel('highlight-job')->warning("[HIGHLIGHT TRIM WARN] ffprobe failed to get duration, proceeding with fallback behavior", [
                    'stderr' => $ffprobe->getErrorOutput()
                ]);
            }

            Log::channel('highlight-job')->info("[HIGHLIGHT TRIM DEBUG] receivedDuration (seconds)", [
                'received_duration' => $receivedDuration
            ]);

            $date = now()->format('dmy');
            $outputDir = storage_path('app/public/highlights');
            @mkdir($outputDir, 0777, true);

            $outputFile = "{$outputDir}/{$this->videoName}_{$this->cameraKey}_{$date}_HL.mp4";

            if ($receivedDuration !== null) {
                if ($duration < $receivedDuration) {
                    Log::channel('highlight-job')->info("[HIGHLIGHT TRIM ACTION] Requested duration is smaller than received duration -> perform trim", [
                        'requested' => $duration,
                        'received' => $receivedDuration
                    ]);

                    $success = $recordedSearch->trimVideo($this->inputFile, 0, $duration, $outputFile, false);

                    if (! $success || ! file_exists($outputFile) || filesize($outputFile) < 1024) {
                        Log::channel('highlight-job')->warning('[HIGHLIGHT TRIM WARN] Fast trim failed, fallback to re-encode.', [
                            'camera_key' => $this->cameraKey,
                            'outputFile' => $outputFile
                        ]);

                        $success = $recordedSearch->trimVideo($this->inputFile, 0, $duration, $outputFile, true);
                    }

                    if (! $success || ! file_exists($outputFile) || filesize($outputFile) === 0) {
                        Log::channel('highlight-job')->warning('[HIGHLIGHT TRIM WARN] Trim totally failed or the output is null', [
                            'camera_key' => $this->cameraKey,
                            'outputFile' => $outputFile
                        ]);
                        return;
                    }
                } else {
                    Log::channel('highlight-job')->info("[HIGHLIGHT TRIM SKIP] Requested duration is equal/longer than received duration -> skip trimming and use original file", [
                        'requested' => $duration,
                        'received' => $receivedDuration
                    ]);
                    if (!@copy($this->inputFile, $outputFile)) {
                        Log::channel('highlight-job')->warning("[HIGHLIGHT TRIM WARN] Failed to copy input file to output location, using input file directly", [
                            'input' => $this->inputFile,
                            'output' => $outputFile
                        ]);
                        $outputFile = $this->inputFile;
                    }
                }
            } else {
                Log::channel('highlight-job')->info("[HIGHLIGHT TRIM UNKNOWN] ffprobe unknown -> attempt trim as before", [
                    'requested' => $duration
                ]);

                $success = $recordedSearch->trimVideo($this->inputFile, 0, $duration, $outputFile, false);

                if (! $success || ! file_exists($outputFile) || filesize($outputFile) < 1024) {
                    Log::channel('highlight-job')->warning('[HIGHLIGHT TRIM WARN] Fast trim failed, fallback to re-encode.', [
                        'camera_key' => $this->cameraKey,
                        'outputFile' => $outputFile
                    ]);

                    $success = $recordedSearch->trimVideo($this->inputFile, 0, $duration, $outputFile, true);
                }

                if (! $success || ! file_exists($outputFile) || filesize($outputFile) === 0) {
                    Log::channel('highlight-job')->warning('[HIGHLIGHT TRIM WARN] Trim totally failed or the output is null', [
                        'camera_key' => $this->cameraKey,
                        'outputFile' => $outputFile
                    ]);
                    return;
                }
            }

            Log::channel('highlight-job')->info('[HIGHLIGHT JOB] TrimVideoJob finished successfully', [
                'output' => basename($outputFile),
                'size' => filesize($outputFile)
            ]);

            WatermarkHighlightVideoJob::dispatch(
                $this->recordingId,
                $outputFile,
                $this->videoName,
                $this->cameraKey,
            )->onQueue('camera-highlight-video-watermark');

            Log::channel('highlight-job')->info('[HIGHLIGHT JOB] TrimVideoJob finished (completed)', [
                'camera_key' => $this->cameraKey,
                'recording_id' => $this->recordingId,
            ]);
            return;
        } catch (\Throwable $e) {
            Log::channel('highlight-job')->error('[HIGHLIGHT JOB ERROR] TrimVideoJob failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
