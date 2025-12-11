<?php

namespace App\Jobs\Highlight;

use App\Enums\RecordedVideoType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

use App\Models\Record\RecordedVideo;
use App\Models\Record\Recording;

use App\Enums\RecordingStatus;
use App\Jobs\SendRecordedVideoEmailJob;
use Symfony\Component\Process\Process;

class InsertHighlightedVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ?int $recordingId;
    protected string $videoPath;
    protected string $videoFilename;
    protected string $thumbnailPath;
    protected string $thumbnailFilename;

    public $tries = 1;
    public $timeout = 0;

    /**
     * Create a new job instance.
     */
    public function __construct(
        ?int $recordingId,
        string $videoPath,
        string $videoFilename,
        string $thumbnailPath,
        string $thumbnailFilename
    ) {
        $this->recordingId = $recordingId;
        $this->videoPath = $videoPath;
        $this->videoFilename = $videoFilename;
        $this->thumbnailPath = $thumbnailPath;
        $this->thumbnailFilename = $thumbnailFilename;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        Log::channel('highlight-job')->info("[HIGHLIGHT JOB] InsertRecordedVideoJob started", [
            'recording_id' => $this->recordingId,
            'videoPath' => $this->videoPath,
            'thumbnailPath' => $this->thumbnailPath
        ]);

        if (!file_exists($this->videoPath)) {
            Log::channel('highlight-job')->warning("[HIGHLIGHT INSERT FAIL] Video tidak ditemukan", [
                'videoPath' => $this->videoPath
            ]);
            return;
        }

        $durationStr = null;

        try {
            $process = new Process([
                'ffprobe',
                '-v',
                'error',
                '-show_entries',
                'format=duration',
                '-of',
                'default=noprint_wrappers=1:nokey=1',
                $this->videoPath
            ]);
            $process->run();

            if ($process->isSuccessful()) {
                $durationSec = floatval(trim($process->getOutput()));
                $hours = floor($durationSec / 3600);
                $minutes = floor(($durationSec % 3600) / 60);
                $seconds = round($durationSec % 60);
                $durationStr = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
            }
        } catch (\Throwable $e) {
            Log::channel('highlight-job')->error("[HIGHLIGHT FFPROBE ERROR]", [
                'videoPath' => $this->videoPath,
                'error' => $e->getMessage()
            ]);
        }

        $recordedVideo = RecordedVideo::create([
            'recording_id' => $this->recordingId ? $this->recordingId : null,
            'video_path' => str_replace(storage_path('app/public/'), '', $this->videoPath),
            'video_filename' => $this->videoFilename,
            'thumbnail_path' => str_replace(storage_path('app/public/'), '', $this->thumbnailPath),
            'thumbnail_filename' => $this->thumbnailFilename,
            'video_size' => filesize($this->videoPath),
            'duration' => $durationStr,
            'type' => RecordedVideoType::Highlight,
        ]);

        Log::channel('highlight-job')->info("[HIGHLIGHT JOB] InsertHighlightedVideoJob finished", [
            'recording_id' => $this->recordingId ? $this->recordingId : null,
            'recorded_id' => $recordedVideo->id,
            'video_filename' => $this->videoFilename
        ]);
    }
}
