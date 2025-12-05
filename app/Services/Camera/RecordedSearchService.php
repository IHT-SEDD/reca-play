<?php

namespace App\Services\Camera;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use App\Services\Camera\SupportUtilitiesService;

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

    public ?int $firstSegmentStart = null;

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
    // (ISAPI METHOD) SEARCH ALL PLAYBACK URIS
    // =========================================================
    public function getAllPlaybackUris(): array
    {
        $allUris = [];
        $startTs = (new \DateTime($this->startTime, new \DateTimeZone('UTC')))->getTimestamp();
        $endTs = (new \DateTime($this->endTime, new \DateTimeZone('UTC')))->getTimestamp();

        foreach ($this->manualChannel as $channel) {
            $xmlPayload = $this->buildSearchXmlPayload($channel, gmdate('Y-m-d\TH:i:s\Z', $startTs), gmdate('Y-m-d\TH:i:s\Z', $endTs));
            Log::channel('camera-record')->info("[DEBUG XML PAYLOAD]", [
                'channel' => $channel,
                'payload' => $xmlPayload,
                'expected_start' => gmdate('Y-m-d\TH:i:s\Z', $startTs),
                'expected_end' => gmdate('Y-m-d\TH:i:s\Z', $endTs)
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
                        'status' => $response->status(),
                    ]);
                    continue;
                }

                $xml = @simplexml_load_string($response->body());
                if (!$xml || !isset($xml->matchList)) continue;

                $uris = SupportUtilitiesService::extractUrisFromXml($xml, $startTs, $endTs);

                if ($uris) {
                    $allUris["camera_{$channel}"] = $uris;
                    Log::channel('camera-record')->info("[SEARCH OK]", [
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

        return $allUris;
    }

    // =========================================================
    // (ISAPI METHOD) DOWNLOAD RAW VIDEO SEGMENTS (per channel)
    // =========================================================
    public function downloadByPlaybackUris(
        array $allUris,
        int $fieldId,
        ?int $userId,
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

        $cameraKey = array_key_first($allUris);
        $uris = $allUris[$cameraKey] ?? [];

        if (empty($uris)) return null;

        $directSegment = collect($uris)->firstWhere('directUse', true);
        $tmpDir = storage_path("app/tmp_recordings/" . uniqid("{$cameraKey}_"));
        @mkdir($tmpDir, 0777, true);

        $concatFile = null;
        $firstSegmentStart = false;

        if ($directSegment) {
            $encodedSegments = $this->downloadAndEncodeSegments($cameraKey, [$directSegment], $tmpDir);

            if (!empty($encodedSegments)) {
                $concatFile = $encodedSegments[0]['file'] ?? null;
                $firstSegmentStart = $encodedSegments[0]['startTime'] ?? null;
            }
        } else {
            $encodedSegments = $this->downloadAndEncodeSegments($cameraKey, $uris, $tmpDir);

            if (!empty($encodedSegments)) {
                $concatFile = $this->concatEncodedSegments($cameraKey, $encodedSegments, $tmpDir);
                $firstSegmentStart = $encodedSegments[0]['startTime'] ?? null;
            }
        }

        $this->firstSegmentStart = $firstSegmentStart;

        return $concatFile;
    }

    // =========================================================
    // TRIM FINAL VIDEO
    // =========================================================
    public function trimVideo(string $inputFile, int $startSec, int $duration, string $outputFile, bool $forceEncode = false): bool
    {
        if (!file_exists($inputFile) || filesize($inputFile) < 1024) return false;

        $cmd = $cmd = SupportUtilitiesService::buildTrimCommand($inputFile, $outputFile, $startSec, $duration, $forceEncode);

        Log::channel('camera-record')->info("[TRIM START]", [
            'input' => $inputFile,
            'output' => $outputFile,
            'startSec' => $startSec,
            'duration' => $duration,
            'forceEncode' => $forceEncode,
            'cmd' => implode(' ', $cmd),
            'inputSize' => file_exists($inputFile) ? filesize($inputFile) : 0,
        ]);

        $process = new Process($cmd);
        $process->setTimeout(0)->run();

        if (!$process->isSuccessful() || !file_exists($outputFile) || filesize($outputFile) < 1024) {
            Log::channel('camera-record')->error("[TRIM FAIL]", [
                'stderr' => trim($process->getErrorOutput())
            ]);
            return false;
        }

        Log::channel('camera-record')->info("[TRIM DONE]", [
            'output' => $outputFile,
            'size' => filesize($outputFile)
        ]);
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
    // DOWNLOAD SEGMENTS HELPERS
    // =========================================================
    private function downloadAndEncodeSegments(string $cameraKey, array $uris, string $tmpDir): array
    {
        $downloadService = new \App\Services\Camera\DownloadVideoService(
            $this->host,
            $this->user,
            $this->pass
        );

        $encodedFiles = [];
        $processes = [];
        $maxParallel = 4;
        $seq = 1;

        foreach ($uris as $u) {
            $uri = $u['uri'];
            $segStartTime = $u['start'] ?? null;

            $rawTs = "{$tmpDir}/seg_{$seq}.ts";
            $encodedMp4 = "{$tmpDir}/seg_{$seq}.mp4";

            Log::channel('camera-record')->info("[DOWNLOAD SEGMENT] Start ISAPI", [
                'camera' => $cameraKey,
                'segment' => $seq,
                'uri' => $uri
            ]);

            $downloadOk = $downloadService->downloadViaISAPI($uri, $rawTs);

            if (!$downloadOk || !file_exists($rawTs) || filesize($rawTs) < 1024) {
                Log::error("[DOWNLOAD SEG FAIL] seg {$seq}");
                $seq++;
                continue;
            }

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
                $rawTs
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
                $rawTs
            ]))->mustRun()->getOutput());

            if ($videoCodec === 'h264' && $audioCodec === 'aac') {
                // $encodedFiles[] = $rawTs;
                $encodedFiles[] = ['file' => $rawTs, 'startTime' => $segStartTime];
            } else {
                $process = new Process([
                    'ffmpeg',
                    '-y',
                    '-err_detect',
                    'ignore_err',
                    '-i',
                    $rawTs,
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
                    $encodedMp4
                ]);

                $process->setTimeout(0)->start();

                $processes[] = [
                    'process' => $process,
                    'raw' => $rawTs,
                    'mp4' => $encodedMp4,
                    'startTime' => $segStartTime
                ];
            }

            while (count($processes) >= $maxParallel) {
                foreach ($processes as $key => $p) {
                    if (!$p['process']->isRunning()) {
                        if ($p['process']->isSuccessful() && file_exists($p['mp4'])) {
                            // $encodedFiles[] = $p['mp4'];
                            $encodedFiles[] = ['file' => $p['mp4'], 'startTime' => $p['startTime']];
                        }
                        @unlink($p['raw']);
                        unset($processes[$key]);
                    }
                }
                usleep(100000);
            }

            $seq++;
        }

        foreach ($processes as $p) {
            $p['process']->wait();
            if ($p['process']->isSuccessful() && file_exists($p['mp4'])) {
                // $encodedFiles[] = $p['mp4'];
                $encodedFiles[] = ['file' => $p['mp4'], 'startTime' => $p['startTime']];
            }
            @unlink($p['raw']);
        }

        return $encodedFiles;
    }

    // =========================================================
    // CONCAT SEGMENTS HELPERS
    // =========================================================
    private function concatEncodedSegments(string $cameraKey, array $encodedFiles, string $tmpDir): ?string
    {
        if (empty($encodedFiles)) return null;

        $listFile = "{$tmpDir}/list.txt";

        $lines = array_map(
            fn($f) => "file '{$f['file']}'",
            $encodedFiles
        );

        file_put_contents($listFile, implode("\n", $lines));

        $finalFile = "{$tmpDir}/final_{$cameraKey}.mp4";

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
            '-movflags',
            '+faststart',
            $finalFile
        ]);
        $concat->setTimeout(0)->run();

        foreach ($encodedFiles as $f) {
            $filePath = $f['file'] ?? null;
            if ($filePath && is_file($filePath)) {
                @unlink($filePath);
            }
        }

        @unlink($listFile);

        if (!$concat->isSuccessful() || !file_exists($finalFile) || filesize($finalFile) < 1024) {
            Log::channel('camera-record')->error("[FFMPEG CONCAT FAIL]", [
                'stderr' => $concat->getErrorOutput()
            ]);
            return null;
        }

        Log::channel('camera-record')->info('[CONCAT OK]', [
            'file' => basename($finalFile),
            'size' => filesize($finalFile)
        ]);

        return $finalFile;
    }
}
