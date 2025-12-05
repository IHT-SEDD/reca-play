<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessDownloadVideo;
use App\Services\Camera\DownloadVideoService;
use App\Services\Camera\RecordedSearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class DownloadVideoController extends Controller
{
    public function index()
    {
        return view('pages.get-video.index');
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
            'status' => 'success',
            'message' => 'Processing the video...',
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

    public function searchVideo(Request $request)
    {
        $validated = $request->validate([
            'host' => 'required|string',
            'username' => 'required|string',
            'password' => 'required|string',
            'channel' => 'required|integer',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
        ]);

        $user = $validated['username'];
        $pass = $validated['password'];

        $startUtc = Carbon::parse($validated['start_time'])->format('Y-m-d\TH:i:s\Z');
        $endUtc = Carbon::parse($validated['end_time'])->format('Y-m-d\TH:i:s\Z');

        $xmlPayload = $this->buildSearchXmlPayload(
            $validated['channel'],
            $startUtc,
            $endUtc
        );

        try {
            $response = Http::withOptions([
                'verify'  => false,
                'timeout' => 30,
                'auth' => [$user, $pass, 'digest'],
            ])->withHeaders([
                'Content-Type' => 'application/xml',
            ])->withBody($xmlPayload, 'application/xml')
                ->post("https://{$validated['host']}/ISAPI/ContentMgmt/search");
        } catch (\Exception $e) {
            return response("Connection failed: " . $e->getMessage(), 500)
                ->header('Content-Type', 'text/plain');
        }

        if (!$response->ok()) {
            return response($response->body(), $response->status())
                ->header('Content-Type', 'application/xml');
        }

        return response($response->body(), 200)
            ->header('Content-Type', 'application/xml');
    }

    private function buildSearchXmlPayload(string $channel, string $start_time, string $end_time): string
    {
        $searchId = strtoupper(Str::uuid()->toString());

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<CMSearchDescription version="1.0" xmlns="http://www.hikvision.com/ver20/XMLSchema">
    <searchID>{$searchId}</searchID>
    <trackIDList>
        <trackID>{$channel}</trackID>
    </trackIDList>
    <timeSpanList>
        <timeSpan>
            <startTime>{$start_time}</startTime>
            <endTime>{$end_time}</endTime>
        </timeSpan>
    </timeSpanList>
    <maxResults>40</maxResults>
    <searchResultPosition>0</searchResultPosition>
</CMSearchDescription>
XML;
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
