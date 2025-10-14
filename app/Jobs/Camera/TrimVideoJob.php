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
        if (!file_exists($this->inputFile)) return;

        try {
            Log::channel('camera-record')->info("[JOB] TrimVideoJob started", [
                'camera_key' => $this->cameraKey,
                'recording_id' => $this->recordingId,
            ]);

            $startSec = strtotime($this->startTime);
            $endSec = strtotime($this->endTime);
            $duration = max(0, $endSec - $startSec);
            if ($duration <= 0) {
                Log::channel('camera-record')->warning("[JOB] TrimVideoJob invalid duration", [
                    'start' => $this->startTime,
                    'end' => $this->endTime
                ]);
                return;
            }

            $date = now()->format('dmy');
            $outputDir = storage_path('app/public/recordings');
            @mkdir($outputDir, 0777, true);
            $outputFile = "{$outputDir}/{$this->cameraKey}_{$this->videoName}_{$date}.mp4";

            $process = $recordedSearch->trimVideo($this->inputFile, $startSec, $duration, $outputFile);
            if (!$process) {
                Log::channel('camera-record')->warning("[JOB] TrimVideoJob failed ffmpeg", [
                    'camera_key' => $this->cameraKey
                ]);
                return;
            }

            Log::channel('camera-record')->info("[JOB] TrimVideoJob finished successfully", [
                'output' => basename($outputFile),
            ]);

            $thumbnailDir = storage_path('app/public/thumbnails');
            @mkdir($thumbnailDir, 0777, true);
            $thumbnailFile = "{$thumbnailDir}/{$this->cameraKey}_{$this->videoName}_{$date}_thumb.jpg";
            ThumbnailVideoJob::dispatch($outputFile, $thumbnailFile)
                ->onQueue('camera-record-video-thumb');

            InsertRecordedVideoJob::dispatch(
                $this->recordingId,
                $outputFile,
                basename($outputFile),
                $thumbnailFile,
                basename($thumbnailFile)
            )->onQueue('camera-record-video-insert');

            $trimmed = $recordedSearch->trimVideo(
                $this->inputFile,
                $this->startTime,
                $this->endTime,
                $this->videoName,
                $this->cameraKey
            );
        } catch (\Throwable $e) {
            Log::channel('camera-record')->error("[JOB ERROR] TrimVideoJob failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
