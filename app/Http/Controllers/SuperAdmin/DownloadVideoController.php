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
        $validated = $request->validate([
            'host' => 'required|string',
            'username' => 'required|string',
            'password' => 'required|string',
            'uri' => 'required|string',
        ]);

        $saveDir = storage_path('app/temp_downloads');

        if (!is_dir($saveDir)) {
            mkdir($saveDir, 0777, true);
            Log::info("[DownloadVideo] Folder created: {$saveDir}");
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
            Log::error("[DownloadVideo] Failed to download from NVR");
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to download video from NVR'
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

            Log::info("[DownloadVideo] Codec detected", [
                'videoCodec' => $videoCodec,
                'audioCodec' => $audioCodec
            ]);
        } catch (\Throwable $e) {
            Log::error("[DownloadVideo] ffprobe error: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to analyze codecs'
            ], 500);
        }

        $isCorrectFormat = ($videoCodec === 'h264' && $audioCodec === 'aac');

        if (!$isCorrectFormat) {
            $encodedFile = $saveDir . '/encoded_' . time() . '.mp4';

            Log::info("[DownloadVideo] Start encoding", [
                'source' => $rawFile,
                'output' => $encodedFile
            ]);

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

                Log::info("[DownloadVideo] Encoding finished", [
                    'output' => $encodedFile
                ]);

                unlink($rawFile);
                $rawFile = $encodedFile;
                $fileName = basename($encodedFile);
            } catch (\Throwable $e) {
                Log::error("[DownloadVideo] ffmpeg error: " . $e->getMessage());
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to encode video'
                ], 500);
            }
        } else {
            Log::info("[DownloadVideo] No encoding needed, codec already correct");
        }

        Log::info("[DownloadVideo] Sending file to browser", [
            'file' => $rawFile
        ]);

        return response()->download(
            $rawFile,
            $fileName,
            ['Content-Type' => 'video/mp4']
        )->deleteFileAfterSend(true);

        // $publicUrl = asset("storage/recordings/" . $fileName);

        // Log::info("[DownloadVideo] Process complete", [
        //     'file' => $rawFile,
        //     'url' => $publicUrl
        // ]);

        // return response()->json([
        //     'status' => 'success',
        //     'message' => $isCorrectFormat
        //         ? 'Video downloaded successfully (already H264 + AAC)'
        //         : 'Video downloaded & converted to H264 + AAC',
        //     'file_name' => $fileName,
        //     'file_path' => $rawFile,
        //     'public_url' => $publicUrl,
        //     'video_codec' => $videoCodec,
        //     'audio_codec' => $audioCodec,
        //     'converted' => !$isCorrectFormat
        // ]);
    }
}
