<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\Camera\DownloadVideoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class DownloadVideoController extends Controller
{
    public function index()
    {
        return view('pages.download-video.index');
    }

    public function addData(Request $request)
    {
        set_time_limit(0);
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '-1');

        $validated = $request->validate([
            'host' => 'required|string',
            'username' => 'required|string',
            'password' => 'required|string',
            'uri' => 'required|string',
        ]);

        $saveDir = storage_path('app/temp_downloads');
        if (!is_dir($saveDir)) {
            mkdir($saveDir, 0777, true);
        }

        $randomIndex = Str::upper(Str::random(6));
        $fileName = "video_" . time() . "_{$randomIndex}.mp4";
        $rawFile = $saveDir . '/' . $fileName;

        Log::info("[DownloadVideo] Start download", [
            'host' => $validated['host'],
            'uri' => $validated['uri'],
            'output' => $rawFile
        ]);

        $service = new DownloadVideoService(
            $validated['host'],
            $validated['username'],
            $validated['password']
        );

        $isDownloaded = $service->downloadViaISAPI(
            $validated['uri'],
            $rawFile
        );

        if (!$isDownloaded) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to download video'
            ], 500);
        }

        Log::info("[DownloadVideo] Download complete", [
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
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to analyze codecs'
            ], 500);
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
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to encode video'
                ], 500);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Video ready for download',
            'file_name' => $fileName,
            'redirect' => route('download-video.file', ['filename' => $fileName])
        ]);
    }

    public function downloadFile($filename)
    {
        set_time_limit(0);
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '-1');

        $filePath = storage_path('app/temp_downloads/' . $filename);

        if (!file_exists($filePath)) {
            return abort(404, 'File not found');
        }

        return response()->streamDownload(function () use ($filePath) {
            $fp = fopen($filePath, 'rb');

            while (!feof($fp)) {
                echo fread($fp, 1024 * 1024);
                flush();
                @ob_flush();
            }

            fclose($fp);
        }, $filename, [
            'Content-Type' => 'video/mp4',
            'Content-Length' => filesize($filePath),
        ]);
    }
}
