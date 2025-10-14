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

            Log::channel('camera-record')->info("[DEBUG XML PAYLOAD]", [
                'channel' => $channel,
                'payload' => $xmlPayload,
                'expected_start' => $start,
                'expected_end' => $end
            ]);

            try {
                $response = Http::withOptions([
                    'verify' => false,
                    'timeout' => 30,
                    'auth' => [$this->user, $this->pass, 'digest']
                ])->withHeaders([
                    'Content-Type' => 'application/xml'
                ])->withBody($xmlPayload, 'application/xml')
                    ->post("https://{$this->host}/ISAPI/ContentMgmt/search");

                Log::channel('camera-record')->info("[DEBUG XML RESPONSE FULL]", [
                    'channel' => $channel,
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                if (!$response->successful()) {
                    Log::channel('camera-record')->warning('[SEARCH FAIL] Non-success HTTP', [
                        'channel' => $channel,
                        'status'  => $response->status(),
                    ]);
                    continue;
                }

                $xml = @simplexml_load_string($response->body());
                if (!$xml || !isset($xml->matchList)) continue;

                $uris = collect($xml->matchList->searchMatchItem ?? [])
                    ->map(fn($i) => (string) $i->mediaSegmentDescriptor->playbackURI)
                    ->filter()
                    ->values()
                    ->toArray();

                if ($uris) {
                    $allUris["camera_{$channel}"] = $uris;
                    Log::channel('camera-record')->info("[SEARCH OK] Found URIs", [
                        'channel' => $channel,
                        'count' => count($uris),
                        'uris' => $uris
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

        // $date = now()->format('dmy');
        $cameraKey = array_key_first($allUris);
        $uris = $allUris[$cameraKey] ?? [];

        if (empty($uris)) {
            Log::channel('camera-record')->warning("[DOWNLOAD] No URIs for {$cameraKey}");
            return null;
        }

        $client = new \GuzzleHttp\Client([
            'verify' => false,
            'auth' => [$this->user, $this->pass, 'digest'],
            'timeout' => 0,
        ]);

        $uris = collect($uris)
            ->sortBy(fn($uri) => $this->extractStartTimeFromUri($uri))
            ->values()
            ->toArray();

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
                $convert->setTimeout(0)->run();

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
        $concat->setTimeout(0)->run();

        if (!$concat->isSuccessful()) {
            Log::channel('camera-record')->error("[FFMPEG CONCAT FAIL] {$cameraKey}", [
                'error' => $concat->getErrorOutput(),
            ]);
            return null;
        }

        foreach ([$listFile, ...$rawFiles] as $f) {
            if (is_file($f)) {
                @unlink($f);
            }
        }

        Log::channel('camera-record')->info('[DOWNLOAD OK] ' . $cameraKey, [
            'file' => basename($concatFile),
        ]);

        return $concatFile;
    }

    // =========================================================
    // STEP 3: TRIM FINAL VIDEO
    // =========================================================
    public function trimVideo(string $inputFile, int $startSec, int $duration, string $outputFile): bool
    {
        if (!file_exists($inputFile) || filesize($inputFile) < 1024) return false;

        $process = new \Symfony\Component\Process\Process([
            'ffmpeg',
            '-y',
            '-ss',
            (string)$startSec,
            '-i',
            $inputFile,
            '-t',
            (string)$duration,
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
        $process->setTimeout(0)->run();

        if (!$process->isSuccessful() || !file_exists($outputFile) || filesize($outputFile) === 0) {
            Log::channel('camera-record')->error("[TRIM FAIL]", [
                'input' => $inputFile,
                'output' => $outputFile,
                'error' => $process->getErrorOutput()
            ]);
            return false;
        }

        return true;
    }

    // =========================================================
    // THUMBNAIL GENERATION
    // =========================================================
    public function generateThumbnail(string $videoPath, string $thumbnailPath): void
    {
        @mkdir(dirname($thumbnailPath), 0777, true);

        if (!file_exists($videoPath) || filesize($videoPath) < 100) {
            Log::channel('camera-record')->error("[THUMB FAIL] Video tidak valid: {$videoPath}");
            return;
        }

        $process = new Process([
            'ffmpeg',
            '-y',
            '-i',
            $videoPath,
            '-vf',
            'thumbnail,scale=320:-1',
            '-frames:v',
            '1',
            $thumbnailPath
        ]);
        $process->setTimeout(0)->run();

        if (!$process->isSuccessful()) {
            Log::channel('camera-record')->error("[THUMB FAIL]", ['error' => $process->getErrorOutput()]);
        } else {
            Log::channel('camera-record')->info("[THUMB OK]", ['path' => $thumbnailPath]);
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

    // =========================================================
    // EXTRACT TIME URI HELPERS
    // =========================================================
    protected function extractStartTimeFromUri(string $uri): int
    {
        preg_match('/starttime=(\d{8}T\d{6})Z?/', $uri, $matches);
        if (isset($matches[1])) {
            $dt = \DateTime::createFromFormat('Ymd\THis', $matches[1], new \DateTimeZone('UTC'));
            return $dt ? $dt->getTimestamp() : 0;
        }
        return 0;
    }
}
