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

    protected string $videoFile;

    public $tries = 2;
    public $timeout = 0;
    public $backoff = [60, 120];

    /**
     * Create a new job instance.
     */
    public function __construct(string $videoFile)
    {
        $this->videoFile = $videoFile;
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

            sleep(1);
            clearstatcache();

            // if ($process->isSuccessful() && file_exists($wmOutput) && filesize($wmOutput) > 0) {
            //     Log::channel('camera-job')->info('[WM] Watermark success, replacing original');

            //     @unlink($this->videoFile);
            //     rename($wmOutput, $this->videoFile);
            // } else {
            //     Log::channel('camera-job')->warning('[WM] FAILED: using original file', [
            //         'error' => $process->getErrorOutput()
            //     ]);
            // }
            if (file_exists($wmOutput) && filesize($wmOutput) > 50000) {
                Log::channel('camera-job')->info('[WM] Watermark success, replacing original', [
                    'exit_code' => $process->getExitCode()
                ]);

                @unlink($this->videoFile);
                rename($wmOutput, $this->videoFile);
            } else {
                Log::channel('camera-job')->warning('[WM] FAILED: using original file', [
                    'exit_code' => $process->getExitCode(),
                    'stderr' => $process->getErrorOutput(),
                    'stdout' => $process->getOutput(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::channel('camera-job')->error('[WM ERROR] Watermark error', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
