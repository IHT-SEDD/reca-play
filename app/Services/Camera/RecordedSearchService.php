<?php

namespace App\Services\Camera;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class RecordedSearchService
{
    private ?string $host = null;
    private ?array $manualChannel = null;
    private ?string $user = null;
    private ?string $pass = null;
    private ?string $startTime = null;
    private ?string $endTime = null;
    private array $cameras = [];

    protected PrepareDataService $prepareData;

    public function __construct(PrepareDataService $prepareData)
    {
        $this->prepareData = $prepareData;
    }

    // =========================================================
    // INITIALIZATION
    // =========================================================
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

        Log::channel('camera-record')->info("[RECORD INIT] Initialized", [
            'fieldId' => $fieldId,
            'host' => $this->host,
            'channels' => $this->manualChannel,
            'startTime' => $this->startTime,
            'endTime' => $this->endTime,
        ]);
    }

    // =========================================================
    // STEP 1: SEARCH ALL PLAYBACK URIS
    // =========================================================
    public function getAllPlaybackUris(): array
    {
        $allUris = [];
        $tz = new \DateTimeZone(config('app.timezone'));
        $start = (new \DateTime($this->startTime, $tz))->format('Y-m-d\TH:i:s\Z');
        $end = (new \DateTime($this->endTime, $tz))->format('Y-m-d\TH:i:s\Z');

        foreach ($this->manualChannel as $channel) {
            $xmlPayload = $this->buildSearchXmlPayload($channel, $start, $end);
            try {
                $response = Http::withOptions([
                    'verify' => false,
                    'timeout' => 30,
                    'auth' => [$this->user, $this->pass, 'digest']
                ])->withHeaders([
                    'Content-Type' => 'application/xml'
                ])->withBody($xmlPayload, 'application/xml')
                    ->post("https://{$this->host}/ISAPI/ContentMgmt/search");

                /** Uncomment this log for debugging the response XML ISAPI Search video */
                Log::channel('camera-record')->info("[RECORD SEARCH] Response XML for channel {$channel}", [
                    'payload_sent' => $xmlPayload,
                    'status' => $response->status(),
                    'response_body_snippet' => mb_substr($response->body(), 0, 1000),
                ]);

                if (!$response->successful()) {
                    Log::channel('camera-record')->warning("[SEARCH FAIL] Non-success HTTP", [
                        'channel' => $channel,
                        'status' => $response->status(),
                    ]);
                    continue;
                }

                $xml = @simplexml_load_string($response->body());
                if (!$xml || !isset($xml->matchList)) continue;

                // $xml->registerXPathNamespace('ns', 'http://www.hikvision.com/ver20/XMLSchema');

                $uris = collect($xml->matchList->searchMatchItem ?? [])
                    ->map(fn($i) => (string) $i->mediaSegmentDescriptor->playbackURI)
                    ->filter()
                    ->values()
                    ->toArray();

                if ($uris) {
                    $allUris["camera_{$channel}"] = $uris;
                    Log::channel('camera-record')->info("[SEARCH OK] Found URIs", [
                        'channel' => $channel,
                        'count' => count($uris)
                    ]);
                }
            } catch (\Throwable $e) {
                Log::channel('camera-record')->error("[SEARCH ERROR] {$channel}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // return $playbackUris;
        return $allUris;
    }

    // =========================================================
    // STEP 2: DOWNLOAD RAW VIDEO SEGMENTS (per camera)
    // =========================================================
    public function downloadByPlaybackUris(
        array $allUris,
        int $fieldId,
        int $userId,
        string $videoName,
        string $host,
        string $user,
        string $pass,
        string $startTime,
        string $endTime
    ): ?string {
        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        $this->startTime = $startTime;
        $this->endTime = $endTime;

        $date = now()->format('dmy');
        $cameraKey = array_key_first($allUris);
        $uris = $allUris[$cameraKey];

        if (empty($uris)) {
            Log::channel('camera-record')->warning("[DOWNLOAD] No URIs for {$cameraKey}");
            return null;
        }

        $client = new \GuzzleHttp\Client([
            'verify' => false,
            'auth' => [$this->user, $this->pass, 'digest'],
            'timeout' => 0,
        ]);

        $tmpDir = storage_path("app/tmp_recordings/" . uniqid("{$cameraKey}_"));
        @mkdir($tmpDir, 0777, true);

        $downloadUrl = "https://{$this->host}/ISAPI/ContentMgmt/download";
        $rawFiles = [];
        $seq = 1;

        foreach ($uris as $uri) {
            $rawPs = "{$tmpDir}/seg_{$seq}.ps";
            $rawTs = "{$tmpDir}/seg_{$seq}.ts";
            $xml = $this->buildDownloadXmlPayload($uri, $this->user, $this->pass);

            try {
                // Download raw stream (.ps)
                $client->post($downloadUrl, [
                    'headers' => ['Content-Type' => 'application/xml'],
                    'body' => $xml,
                    'sink' => $rawPs,
                ]);

                // Convert to TS
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
                if ($convert->isSuccessful()) {
                    $rawFiles[] = $rawTs;
                    @unlink($rawPs);
                } else {
                    Log::channel('camera-record')->error("[FFMPEG FAIL] seg_{$seq}", [
                        'error' => $convert->getErrorOutput()
                    ]);
                }
            } catch (\Throwable $e) {
                Log::channel('camera-record')->error("[DOWNLOAD ERROR] {$cameraKey} seg {$seq}", [
                    'error' => $e->getMessage(),
                ]);
            }
            $seq++;
        }

        if (empty($rawFiles)) return null;

        // Concat file list
        $listFile = "{$tmpDir}/list.txt";
        file_put_contents($listFile, implode("\n", array_map(fn($f) => "file '{$f}'", $rawFiles)));

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
            return null;
        }

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
            return null;
        }

        foreach ([$listFile, $concatFile, ...$rawFiles] as $f) @unlink($f);
        @rmdir($tmpDir);

        Log::channel('camera-record')->info("[DOWNLOAD OK] {$cameraKey}", [
            'file' => basename($finalFile)
        ]);

        return $finalFile;
    }

    // =========================================================
    // STEP 3: TRIM FINAL VIDEO
    // =========================================================
    public function trimVideo(string $inputFile, string $startTime, string $endTime, string $videoName, string $cameraKey): ?string
    {
        $startSec = strtotime($startTime);
        $endSec = strtotime($endTime);
        $duration = $endSec - $startSec;

        $outputFile = storage_path('app/public/recordings/trimmed_' . basename($inputFile));
        $process = new Process([
            'ffmpeg',
            '-y',
            '-ss',
            '0',
            '-i',
            $inputFile,
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
            $outputFile
        ]);
        $process->setTimeout(0);
        $process->run();

        if (!$process->isSuccessful()) {
            Log::channel('camera-record')->error("[TRIM FAIL] {$cameraKey}", [
                'error' => $process->getErrorOutput()
            ]);
            return null;
        }

        Log::channel('camera-record')->info("[TRIM OK] {$cameraKey}", [
            'output' => basename($outputFile),
            'duration' => $duration
        ]);

        return $outputFile;
    }

    // =========================================================
    // THUMBNAIL GENERATION
    // =========================================================
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
        if (!$process->isSuccessful()) {
            Log::channel('camera-record')->error("[THUMB FAIL]", [
                'error' => $process->getErrorOutput()
            ]);
        } else {
            Log::channel('camera-record')->info("[THUMB OK]", [
                'path' => $thumbnailPath
            ]);
        }
    }

    // =========================================================
    // XML PAYLOAD HELPERS
    // =========================================================
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

    // =========================================================
    // CAM CONNECTION HELPERS
    // =========================================================
    public function getCameraConnection(string $cameraKey): ?array
    {
        $channelId = (int) str_replace('camera_', '', $cameraKey);
        $camera = collect($this->cameras)->firstWhere('channel', $channelId);

        if (!$camera) {
            return [
                'host' => $this->host,
                'user' => $this->user,
                'pass' => $this->pass,
            ];
        }

        return [
            'host' => $camera['host'] ?? $this->host,
            'user' => $camera['user'] ?? $this->user,
            'pass' => $camera['pass'] ?? $this->pass,
        ];
    }
}
