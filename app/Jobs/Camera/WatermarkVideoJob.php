<?php

namespace App\Jobs\Camera;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class WatermarkVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $recordingId;
    protected string $videoFile;
    protected string $videoName;
    protected string $cameraKey;

    public $tries = 2;
    public $timeout = 0;
    public $backoff = [60, 120];

    /**
     * Create a new job instance.
     */
    public function __construct(int $recordingId, string $videoFile, string $videoName, string $cameraKey)
    {
        $this->recordingId = $recordingId;
        $this->videoFile = $videoFile;
        $this->videoName = $videoName;
        $this->cameraKey = $cameraKey;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $watermarkFile = public_path('assets/img/logos/reca-white.png');

            Log::channel('camera-job')->info('[WM] Checking watermark file', [
                'file' => $watermarkFile,
                'exists' => file_exists($watermarkFile)
            ]);

            if (!file_exists($watermarkFile)) {
                Log::channel('camera-job')->warning('[WM] Watermark file missing, skipped.');
                return;
            }

            $wmOutput = str_replace('.mp4', '_wm.mp4', $this->videoFile);
            if ($wmOutput === $this->videoFile) {
                $wmOutput = $this->videoFile . "_wm.mp4";
            }

            Log::channel('camera-job')->info('[WM] Applying watermark...', [
                'source' => $this->videoFile,
                'target' => $wmOutput
            ]);

            $process = new Process([
                'ffmpeg',
                '-y',
                '-i',
                $this->videoFile,
                '-i',
                $watermarkFile,
                '-filter_complex',
                "[1]scale=120:-1[wm];[0][wm]overlay=(W-w)/2:H-h-20:format=auto:alpha=0.35",
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

            sleep(1);
            clearstatcache();

            if (!file_exists($wmOutput) || filesize($wmOutput) < 20000) {
                Log::channel('camera-job')->warning('[WM] Watermark FAILED.', [
                    'stderr' => $process->getErrorOutput()
                ]);
                return;
            }

            @unlink($this->videoFile);
            rename($wmOutput, $this->videoFile);

            $date = now()->format('dmy');
            $thumbnailDir = storage_path('app/public/thumbnails');
            @mkdir($thumbnailDir, 0777, true);
            $thumbnailFile = "{$thumbnailDir}/{$this->videoName}_{$this->cameraKey}_{$date}_thumb.jpg";

            ThumbnailVideoJob::dispatch(
                $this->recordingId,
                $this->videoFile,
                $thumbnailFile
            )->onQueue('camera-record-video-thumb');
        } catch (\Throwable $e) {
            Log::channel('camera-job')->error('[WM ERROR] Watermark error', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
