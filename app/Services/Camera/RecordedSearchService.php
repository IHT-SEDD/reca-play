<?php

namespace App\Services\Camera;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class RecordedSearchService
{
    private $host;
    private $manualChannel;
    private $user;
    private $pass;
    private $startTime;
    private $endTime;
    private $cameras = [];

    protected PrepareDataService $prepareData;

    public function __construct(PrepareDataService $prepareData)
    {
        $this->prepareData = $prepareData;
    }

    // ========== Initialize data ==========
    public function initialize(int $fieldId, string $startTime, string $endTime): void
    {
        $data = $this->prepareData->prepare($fieldId);

        $this->host = $data['host'];
        $this->manualChannel = $data['manualChannel'];
        $this->user = $data['user'];
        $this->pass = $data['pass'];
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->cameras = $data['cameras'];

        Log::channel('camera-record')->info("[RECORD SEARCH] Search recorded videos initialized", [
            'host' => $this->host,
            'channel' => $this->manualChannel,
            'startTime' => $this->startTime,
            'endTime' => $this->endTime,
            'cameras' => $this->cameras,
        ]);
    }

    // ========== Search recording ==========
    public function getAllPlaybackUris(): array
    {
        // $playbackUris = [];
        $allUris = [];
        $tz = new \DateTimeZone(config('app.timezone'));

        Log::channel('camera-record')->info("[RECORD SEARCH] Raw times", [
            'raw_startTime' => $this->startTime,
            'raw_endTime' => $this->endTime,
            'timezone' => config('app.timezone'),
        ]);

        $start = (new \DateTime($this->startTime, $tz))->format('Y-m-d\TH:i:s\Z');
        $end = (new \DateTime($this->endTime, $tz))->format('Y-m-d\TH:i:s\Z');

        Log::channel('camera-record')->info("[RECORD SEARCH] Converted times", [
            'start_utc' => $start,
            'end_utc' => $end,
        ]);

        foreach ($this->manualChannel as $channel) {
            $xmlPayload = $this->buildSearchXmlPayload($channel, $start, $end);

            try {
                $response = Http::withOptions([
                    'verify' => false,
                    'timeout' => 30,
                    'auth' => [$this->user, $this->pass, 'digest']
                ])->withHeaders([
                    'Content-Type' => 'application/xml',
                    'Accept' => '*/*'
                ])->withBody($xmlPayload, 'application/xml')
                    ->post("https://{$this->host}/ISAPI/ContentMgmt/search");

                /** Uncomment this log for debugging the response XML ISAPI Search video */
                Log::channel('camera-record')->info("[RECORD SEARCH] Response XML for channel {$channel}", [
                    'payload_sent' => $xmlPayload,
                    'status' => $response->status(),
                    'response_body_snippet' => mb_substr($response->body(), 0, 1000),
                ]);

                if (!$response->successful()) {
                    Log::channel('camera-record')->warning("[RECORD SEARCH] Non-success status", [
                        'channel' => $channel,
                        'status' => $response->status(),
                        'body' => mb_substr($response->body(), 0, 500),
                    ]);
                    continue;
                }

                $xml = @simplexml_load_string($response->body());
                if (!$xml || !isset($xml->matchList)) continue;

                $xml->registerXPathNamespace('ns', 'http://www.hikvision.com/ver20/XMLSchema');

                $uris = collect($xml->matchList->searchMatchItem ?? [])
                    ->map(fn($item) => (string) $item->mediaSegmentDescriptor->playbackURI)
                    ->filter()
                    ->values()
                    ->toArray();

                if ($uris) {
                    Log::channel('camera-record')->info("[RECORD SEARCH] Found URIs", [
                        'channel' => $channel,
                        'uri_count' => count($uris),
                        'first_uri' => $uris[0] ?? null,
                    ]);
                    $allUris["camera_{$channel}"] = $uris;
                } else {
                    Log::channel('camera-record')->info("[RECORD SEARCH] No URIs found for channel", [
                        'channel' => $channel,
                    ]);
                }
            } catch (\Throwable $e) {
                Log::channel('camera-record')->error("[RECORD SEARCH ERROR] Channel {$channel}: {$e->getMessage()}");
            }
        }

        // return $playbackUris;
        return $allUris;
    }

    // ==================== Download videos to storage ====================
    // public function downloadByPlaybackUris(array $allUris, int $fieldId, int $userId, string $videoName): array
    // {
    //     // array $playbackUris,
    //     $results = [];
    //     $date = now()->format('dmy');

    //     foreach ($allUris as $cameraKey => $playbackUris) {
    //         $downloadUrl = "https://{$this->host}/ISAPI/ContentMgmt/download";
    //         $client = new \GuzzleHttp\Client([
    //             'verify' => false,
    //             'auth' => [$this->user, $this->pass, 'digest'],
    //             'timeout' => 0,
    //         ]);

    //         $rawFiles = [];
    //         $sequence = 1;

    //         Log::channel('camera-record')->info("[RECORD] Start download for {$cameraKey}", [
    //             'segments' => count($playbackUris),
    //             'range' => "{$this->startTime} → {$this->endTime}",
    //         ]);

    //         foreach ($playbackUris as $playbackURI) {
    //             $xmlBody = $this->buildDownloadXmlPayload($playbackURI, $this->user, $this->pass);
    //             // $rawPath = storage_path("app/public/recordings/{$cameraKey}_seg_{$sequence}.ps");
    //             $rawPs = storage_path("app/public/recordings/{$cameraKey}_seg_{$sequence}.ps");
    //             $rawTs = storage_path("app/public/recordings/{$cameraKey}_seg_{$sequence}.ts");

    //             try {
    //                 // @mkdir(dirname($rawPath), 0777, true);
    //                 @mkdir(dirname($rawPs), 0777, true);
    //                 $client->post($downloadUrl, [
    //                     'headers' => [
    //                         'Content-Type' => 'application/xml',
    //                         'Accept' => '*/*'
    //                     ],
    //                     'body' => $xmlBody,
    //                     // 'sink' => $rawPath,
    //                     'sink' => $rawPs,
    //                 ]);

    //                 $convert = new Process([
    //                     'ffmpeg',
    //                     '-y',
    //                     '-i',
    //                     $rawPs,
    //                     '-c:v',
    //                     'libx264',
    //                     '-preset',
    //                     'ultrafast',
    //                     '-tune',
    //                     'zerolatency',
    //                     '-crf',
    //                     '28',
    //                     '-c:a',
    //                     'aac',
    //                     '-b:a',
    //                     '96k',
    //                     '-movflags',
    //                     '+faststart',
    //                     $rawTs
    //                 ]);
    //                 $convert->run();

    //                 if (!$convert->isSuccessful()) {
    //                     Log::channel('camera-record')->error("[FFMPEG CONVERT FAIL] {$cameraKey}_seg_{$sequence}", [
    //                         'error' => $convert->getErrorOutput(),
    //                     ]);
    //                     continue;
    //                 }

    //                 // $rawFiles[] = $rawPath;
    //                 $rawFiles[] = $rawTs;
    //                 @unlink($rawPs);
    //             } catch (\Throwable $e) {
    //                 Log::channel('camera-record')->error("[RECORD DOWNLOAD] Error seg {$sequence}", [
    //                     'playbackURI' => $playbackURI,
    //                     'exception' => $e->getMessage()
    //                 ]);
    //             }
    //             $sequence++;
    //         }

    //         if (empty($rawFiles)) {
    //             Log::channel('camera-record')->warning("[NO FILES] {$cameraKey}");
    //             continue;
    //         }

    //         // $concatFile = storage_path("app/public/recordings/concat_{$videoName}_{$date}.mp4");
    //         // $listFile = storage_path("app/public/recordings/concat_list.txt");

    //         $listFile = storage_path("app/public/recordings/list_{$cameraKey}.txt");
    //         $concatFile = storage_path("app/public/recordings/concat_{$cameraKey}_{$videoName}_{$date}.mp4");

    //         $fileList = implode("\n", array_map(fn($f) => "file '{$f}'", $rawFiles));
    //         file_put_contents($listFile, $fileList);

    //         // $concatFile = storage_path("app/public/recordings/concat_{$cameraKey}_{$videoName}_{$date}.mp4");
    //         // $listFile = storage_path("app/public/recordings/concat_list_{$cameraKey}.txt");

    //         // $fileList = "";
    //         // foreach ($rawFiles as $rf) {
    //         //     $fileList .= "file '" . $rf . "'\n";
    //         // }
    //         // file_put_contents($listFile, $fileList);
    //         // $fileList = collect($rawFiles)->map(fn($f) => "file '{$f}'")->implode("\n");
    //         // file_put_contents($listFile, $fileList);

    //         $concat = new Process([
    //             'ffmpeg',
    //             '-y',
    //             '-f',
    //             'concat',
    //             '-safe',
    //             '0',
    //             '-i',
    //             $listFile,
    //             '-c:v',
    //             // new :begin
    //             'libx264',
    //             '-preset',
    //             'ultrafast',
    //             '-tune',
    //             'zerolatency',
    //             '-crf',
    //             '28',
    //             // new :end
    //             // existing :begin
    //             // 'copy',
    //             // existing :end
    //             '-c:a',
    //             'aac',
    //             '-b:a',
    //             '96k',
    //             // new :begin
    //             '-movflags',
    //             '+faststart',
    //             // new :end
    //             $concatFile
    //         ]);
    //         $concat->run();

    //         if (!$concat->isSuccessful()) {
    //             Log::channel('camera-record')->error("[FFMPEG CONCAT FAIL] {$cameraKey}", [
    //                 'error' => $concat->getErrorOutput(),
    //             ]);
    //             continue;
    //         }

    //         $finalFile = storage_path("app/public/recordings/{$cameraKey}_{$videoName}_{$date}_{$fieldId}{$userId}.mp4");
    //         try {
    //             $this->trimVideo($concatFile, $finalFile, $this->startTime, $this->endTime);
    //             @unlink($concatFile);
    //         } catch (\Throwable $e) {
    //             Log::channel('camera-record')->error("[FFMPEG TRIM FAIL] {$cameraKey}", [
    //                 'error' => $e->getMessage(),
    //             ]);
    //             rename($concatFile, $finalFile);
    //         }

    //         // $finalFile = storage_path("app/public/recordings/{$videoName}_{$date}_{$fieldId}{$userId}.mp4");
    //         // $this->trimVideo($concatFile, $finalFile, $this->startTime, $this->endTime);

    //         // $thumbnailDir = storage_path('app/public/thumbnails');
    //         // @mkdir($thumbnailDir, 0777, true);
    //         // $thumbnailPath = $thumbnailDir . '/' . pathinfo($finalFile, PATHINFO_FILENAME) . '_thumb.jpg';

    //         // $this->generateThumbnail($finalFile, $thumbnailPath);
    //         // $finalFile = storage_path("app/public/recordings/{$cameraKey}_{$videoName}_{$date}_{$fieldId}{$userId}.mp4");

    //         $thumbnailDir = storage_path('app/public/thumbnails');
    //         @mkdir($thumbnailDir, 0777, true);
    //         $thumbnailPath = $thumbnailDir . '/' . pathinfo($finalFile, PATHINFO_FILENAME) . '_thumb.jpg';
    //         $this->generateThumbnail($finalFile, $thumbnailPath);

    //         // cleanup temp
    //         // @unlink($concatFile);
    //         // @unlink($listFile);
    //         // foreach ($rawFiles as $rf) {
    //         //     @unlink($rf);
    //         // }
    //         @unlink($listFile);
    //         foreach ($rawFiles as $rf) {
    //             @unlink($rf);
    //         }

    //         // 
    //         $results[] = [
    //             'camera' => $cameraKey,
    //             'path' => 'recordings/' . basename($finalFile),
    //             'filename' => basename($finalFile),
    //             'size' => filesize($finalFile),
    //             'thumbnail' => 'thumbnails/' . basename($thumbnailPath),
    //         ];
    //     }
    //     return $results;
    // }

    public function downloadByPlaybackUris(array $allUris, int $fieldId, int $userId, string $videoName): array
    {
        $results = [];
        $date = now()->format('dmy');

        foreach ($allUris as $cameraKey => $uris) {
            $downloadUrl = "https://{$this->host}/ISAPI/ContentMgmt/download";
            $client = new \GuzzleHttp\Client([
                'verify' => false,
                'auth' => [$this->user, $this->pass, 'digest'],
                'timeout' => 0,
            ]);

            $tmpDir = storage_path("app/tmp_recordings/" . uniqid());
            @mkdir($tmpDir, 0777, true);

            $rawFiles = [];
            $sequence = 1;

            Log::channel('camera-record')->info("[RECORD] Start download for {$cameraKey}", [
                'segments' => count($uris),
                'range' => "{$this->startTime} → {$this->endTime}",
            ]);

            foreach ($uris as $playbackURI) {
                $rawPs = "{$tmpDir}/{$cameraKey}_seg_{$sequence}.ps";
                $rawTs = "{$tmpDir}/{$cameraKey}_seg_{$sequence}.ts";
                $xmlBody = $this->buildDownloadXmlPayload($playbackURI, $this->user, $this->pass);

                try {
                    // 1️⃣ Download .ps
                    $client->post($downloadUrl, [
                        'headers' => ['Content-Type' => 'application/xml'],
                        'body' => $xmlBody,
                        'sink' => $rawPs,
                    ]);

                    // 2️⃣ Convert PS → TS (fast remux, audio re-encode ke AAC)
                    $convert = new Process([
                        'ffmpeg',
                        '-y',
                        '-i',
                        $rawPs,
                        '-c:v',
                        'copy',
                        '-c:a',
                        'aac',
                        '-b:a',
                        '128k',
                        '-f',
                        'mpegts',
                        $rawTs
                    ]);
                    $convert->run();

                    if (!$convert->isSuccessful()) {
                        Log::channel('camera-record')->error("[FFMPEG CONVERT FAIL] {$cameraKey}_seg_{$sequence}", [
                            'error' => $convert->getErrorOutput(),
                        ]);
                        continue;
                    }

                    $rawFiles[] = $rawTs;
                    @unlink($rawPs);
                } catch (\Throwable $e) {
                    Log::channel('camera-record')->error("[DOWNLOAD ERROR] {$cameraKey} seg {$sequence}: {$e->getMessage()}");
                }
                $sequence++;
            }

            if (empty($rawFiles)) continue;

            // 3️⃣ Buat list file
            $listFile = "{$tmpDir}/list.txt";
            $fileList = implode("\n", array_map(fn($f) => "file '{$f}'", $rawFiles));
            file_put_contents($listFile, $fileList);

            // 4️⃣ Concat TS jadi 1 file
            $concatFile = "{$tmpDir}/concat_{$cameraKey}.ts";
            $concat = new Process([
                'ffmpeg',
                '-y',
                '-f',
                'concat',
                '-safe',
                '0',
                '-i',
                $listFile,
                '-c',
                'copy',
                $concatFile
            ]);
            $concat->run();

            if (!$concat->isSuccessful()) {
                Log::channel('camera-record')->error("[FFMPEG CONCAT FAIL] {$cameraKey}", [
                    'error' => $concat->getErrorOutput(),
                ]);
                continue;
            }

            // 5️⃣ Encode final ke H264 + AAC
            $finalFile = storage_path("app/public/recordings/{$cameraKey}_{$videoName}_{$date}_{$fieldId}{$userId}.mp4");
            $encode = new Process([
                'ffmpeg',
                '-y',
                '-i',
                $concatFile,
                '-c:v',
                'libx264',
                '-preset',
                'fast',
                '-crf',
                '23',
                '-c:a',
                'aac',
                '-b:a',
                '128k',
                '-movflags',
                '+faststart',
                $finalFile
            ]);
            $encode->setTimeout(0);
            $encode->run();

            if (!$encode->isSuccessful()) {
                Log::channel('camera-record')->error("[FFMPEG ENCODE FAIL] {$cameraKey}", [
                    'error' => $encode->getErrorOutput(),
                ]);
                continue;
            }

            // 6️⃣ Generate thumbnail
            $thumbnailDir = storage_path('app/public/thumbnails');
            @mkdir($thumbnailDir, 0777, true);
            $thumbnailPath = $thumbnailDir . '/' . pathinfo($finalFile, PATHINFO_FILENAME) . '_thumb.jpg';
            $this->generateThumbnail($finalFile, $thumbnailPath);

            // 7️⃣ Cleanup
            foreach ([$listFile, $concatFile, ...$rawFiles] as $f) @unlink($f);
            @rmdir($tmpDir);

            Log::channel('camera-record')->info("[RECORD COMPLETE] {$cameraKey}", [
                'output' => basename($finalFile),
            ]);

            $results[] = [
                'camera' => $cameraKey,
                'path' => 'recordings/' . basename($finalFile),
                'filename' => basename($finalFile),
                'size' => filesize($finalFile),
                'thumbnail' => 'thumbnails/' . basename($thumbnailPath),
            ];
        }

        return $results;
    }

    // ========== Generate thumbnail ==========
    public function generateThumbnail(string $videoPath, string $thumbnailPath): void
    {
        @mkdir(dirname($thumbnailPath), 0777, true);

        $process = new Process([
            'ffmpeg',
            '-y',
            '-ss',
            '2',
            '-i',
            $videoPath,
            '-vframes',
            '1',
            $thumbnailPath
        ]);

        $process->run();

        Log::channel('camera-record')->info("[THUMBNAIL GENERATE] THUMBNAIL STDOUT", [$process->getOutput()]);
        Log::channel('camera-record')->error("[THUMBNAIL GENERATE] THUMBNAIL STDERR", [$process->getErrorOutput()]);

        if (!$process->isSuccessful()) {
            Log::channel('camera-record')->error("[THUMBNAIL GENERATE] Failed : ", [$process]);
            throw new ProcessFailedException($process);
        }

        Log::channel('camera-record')->info("[THUMBNAIL GENERATE] Thumbnail generated", [
            'thumbnailPath' => $thumbnailPath,
        ]);
    }

    // ========== Trim video sesuai start-end ==========
    // protected function trimVideo(string $input, string $output, string $start, string $end): void
    // {
    //     $startSec = strtotime($start);
    //     $endSec   = strtotime($end);
    //     $duration = $endSec - $startSec;

    //     $process = new Process([
    //         'ffmpeg',
    //         '-y',
    //         '-ss',
    //         '0',
    //         '-i',
    //         $input,
    //         '-t',
    //         $duration,
    //         // existing :begin
    //         // '-c',
    //         // 'copy',
    //         // existing :end

    //         // new :begin
    //         '-c:v',
    //         'libx264',
    //         '-preset',
    //         'ultrafast',
    //         '-tune',
    //         'zerolatency',
    //         '-crf',
    //         '28',
    //         '-c:a',
    //         'aac',
    //         '-b:a',
    //         '96k',
    //         '-movflags',
    //         '+faststart',
    //         // new :end
    //         $output
    //     ]);
    //     $process->run();

    //     if (!$process->isSuccessful()) {
    //         throw new ProcessFailedException($process);
    //     }
    // }

    protected function trimVideo(string $input, string $output, string $start, string $end): void
    {
        $startSec = strtotime($start);
        $endSec = strtotime($end);
        $duration = $endSec - $startSec;

        $process = new Process([
            'ffmpeg',
            '-y',
            '-ss',
            '0',
            '-i',
            $input,
            '-t',
            $duration,
            '-c:v',
            'libx264',
            '-preset',
            'fast',
            '-crf',
            '23',
            '-c:a',
            'aac',
            '-b:a',
            '128k',
            '-movflags',
            '+faststart',
            $output
        ]);
        $process->setTimeout(0);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        Log::channel('camera-record')->info("[TRIM SUCCESS]", [
            'output' => basename($output),
            'duration_sec' => $duration
        ]);
    }

    // ========== Build XML Payload for searching ==========
    protected function buildSearchXmlPayload(string $channel, string $start, string $end): string
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
            <startTime>{$start}</startTime>
            <endTime>{$end}</endTime>
        </timeSpan>
    </timeSpanList>
    <maxResults>40</maxResults>
    <searchResultPosition>0</searchResultPosition>
</CMSearchDescription>
XML;
    }

    // ========== Build XML Payload for downloading ==========
    protected function buildDownloadXmlPayload(string $playbackURI, string $userName = '', string $password = ''): string
    {
        $userXml = $userName ? "<userName>{$userName}</userName>" : '';
        $passXml = $password ? "<password>{$password}</password>" : '';

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<downloadRequest version="1.0" xmlns="http://www.isapi.org/ver20/XMLSchema">
<playbackURI>{$playbackURI}</playbackURI>
{$userXml}
{$passXml}
</downloadRequest>
XML;
    }
}
