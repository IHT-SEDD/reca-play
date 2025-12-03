<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessDownloadVideo;
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

        $jobId = Str::uuid()->toString();

        cache()->put("download_status_$jobId", [
            'status' => 'processing'
        ], 3600);

        dispatch((new ProcessDownloadVideo(
            $validated['host'],
            $validated['username'],
            $validated['password'],
            $validated['uri'],
            $jobId
        )))->onQueue('video-download');

        return response()->json([
            'status' => 'processing',
            'job_id' => $jobId
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

    public function status($jobId)
    {
        $status = cache()->get("download_status_$jobId");

        if (!$status) {
            return response()->json([
                'status' => 'not_found'
            ], 404);
        }

        return response()->json($status);
    }
}
