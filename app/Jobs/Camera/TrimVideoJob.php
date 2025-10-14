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

    public $queue = 'camera-record-video-trim';
    public $tries = 3;
    public $timeout = 0;

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
        try {
            Log::channel('camera-record')->info("[JOB] TrimVideoJob started", [
                'camera_key' => $this->cameraKey,
                'recording_id' => $this->recordingId,
            ]);

            $trimmed = $recordedSearch->trimVideo(
                $this->inputFile,
                $this->startTime,
                $this->endTime,
                $this->videoName,
                $this->cameraKey
            );

            if ($trimmed) {
                Log::channel('camera-record')->info("[JOB] TrimVideoJob finished successfully", [
                    'output' => basename($trimmed),
                ]);
            } else {
                Log::channel('camera-record')->warning("[JOB] TrimVideoJob failed to produce output", [
                    'camera_key' => $this->cameraKey,
                ]);
            }
        } catch (\Throwable $e) {
            Log::channel('camera-record')->error("[JOB ERROR] TrimVideoJob failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
