<?php

namespace App\Jobs;

use App\Services\Camera\DownloadVideoService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class ProcessDownloadVideo implements ShouldQueue
{
    use Queueable;

    public $queue = 'video-download';
    public $timeout = 0;
    public $tries = 1;

    protected $host, $username, $password, $uri, $jobId;
    /**
     * Create a new job instance.
     */
    public function __construct($host, $username, $password, $uri, $jobId)
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->uri = $uri;
        $this->jobId = $jobId;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $saveDir = storage_path("app/temp_downloads");
        if (!is_dir($saveDir)) {
            mkdir($saveDir, 0777, true);
        }

        $randomIndex = Str::upper(Str::random(6));
        $fileName = "video_" . time() . "_{$randomIndex}.mp4";
        $rawFile = $saveDir . '/' . $fileName;

        Log::info("[QueueDownload:$this->jobId] Start download", [
            'host' => $this->host,
            'uri' => $this->uri,
            'output' => $rawFile
        ]);

        $service = new DownloadVideoService($this->host, $this->username, $this->password);

        $downloaded = $service->downloadViaISAPI($this->uri, $rawFile);

        if (!$downloaded) {
            cache()->put("download_status_{$this->jobId}", [
                'status' => 'error',
                'message' => 'Failed to download video'
            ], 3600);
            return;
        }

        Log::info("[QueueDownload:$this->jobId] Download complete", [
            'file' => $rawFile,
            'size' => filesize($rawFile)
        ]);

        try {
            $videoCodec = trim((new Process([
                'ffprobe',
                '-v',
                'error',
                '-select_streams',
                'v:0',
                '-show_entries',
                'stream=codec_name',
                '-of',
                'default=noprint_wrappers=1:nokey=1',
                $rawFile
            ]))->mustRun()->getOutput());

            $audioCodec = trim((new Process([
                'ffprobe',
                '-v',
                'error',
                '-select_streams',
                'a:0',
                '-show_entries',
                'stream=codec_name',
                '-of',
                'default=noprint_wrappers=1:nokey=1',
                $rawFile
            ]))->mustRun()->getOutput());
        } catch (\Throwable $e) {
            cache()->put("download_status_{$this->jobId}", [
                'status' => 'error',
                'message' => 'Failed to analyze codecs'
            ]);
            return;
        }

        $isCorrectFormat = ($videoCodec === 'h264' && $audioCodec === 'aac');

        if (!$isCorrectFormat) {

            $encodedFileName = "encoded_" . time() . "_{$randomIndex}.mp4";
            $encodedFile = $saveDir . '/' . $encodedFileName;

            try {
                $process = new Process([
                    'ffmpeg',
                    '-y',
                    '-err_detect',
                    'ignore_err',
                    '-i',
                    $rawFile,
                    '-c:v',
                    'libx264',
                    '-preset',
                    'ultrafast',
                    '-crf',
                    '23',
                    '-c:a',
                    'aac',
                    '-b:a',
                    '128k',
                    '-movflags',
                    '+faststart',
                    $encodedFile
                ]);

                $process->setTimeout(0);
                $process->mustRun();

                unlink($rawFile);
                $rawFile = $encodedFile;
                $fileName = $encodedFileName;
            } catch (\Throwable $e) {
                cache()->put("download_status_{$this->jobId}", [
                    'status' => 'error',
                    'message' => 'Failed to encode video'
                ]);
                return;
            }
        }

        cache()->put("download_status_{$this->jobId}", [
            'status' => 'success',
            'file_name' => $fileName,
            'download_url' => route('download-video.file', $fileName)
        ], 3600);
    }
}
