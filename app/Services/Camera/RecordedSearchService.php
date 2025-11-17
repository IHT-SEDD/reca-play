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
    // (RTSP METHOD) RTSP PLAYBACK DETECTION
    // =========================================================
    public function DetectPlaybackRTSP(string $host, string $user, string $pass, array $channels): array
    {
        $results = [];

        foreach ($channels as $channel) {
            $rtspUrl = "rtsp://{$user}:{$pass}@{$host}:554/Streaming/tracks/{$channel}/";

            $cmd = [
                'ffprobe',
                '-v',
                'error',
                '-rtsp_transport',
                'tcp',
                '-i',
                $rtspUrl
            ];

            try {
                $process = new Process($cmd);
                $process->setTimeout(8);
                $process->run();

                $success = $process->isSuccessful();

                Log::channel('camera-record')->info("[RTSP DETECT]", [
                    'channel' => $channel,
                    'rtsp_url' => $rtspUrl,
                    'success' => $success,
                    'stderr' => $process->getErrorOutput()
                ]);

                $results[$channel] = [
                    'success' => $success,
                    'rtsp_url' => $rtspUrl
                ];
            } catch (\Throwable $e) {

                Log::channel('camera-record')->error("[RTSP DETECT ERROR]", [
                    'channel' => $channel,
                    'rtsp_url' => $rtspUrl,
                    'error' => $e->getMessage(),
                ]);

                $results[$channel] = [
                    'success' => false,
                    'rtsp_url' => $rtspUrl
                ];
            }
        }

        return $results;
    }

    // =========================================================
    // (RTSP METHOD) RTSP DOWNLOAD PLAYBACK RANGE
    // =========================================================
    public function DownloadVideoViaRTSP(
        string $rtspUrl,
        string $savePath,
        string $startTime,
        string $endTime,
        int $durationLimit = 0
    ): bool {

        @mkdir(dirname($savePath), 0777, true);

        $start = new \DateTime($startTime, new \DateTimeZone('UTC'));
        $end = new \DateTime($endTime, new \DateTimeZone('UTC'));
        $duration = $end->getTimestamp() - $start->getTimestamp();

        if ($duration <= 0) {
            Log::channel('camera-record')->warning("[RTSP DOWNLOAD] Invalid time range", [
                'start' => $startTime,
                'end' => $endTime
            ]);
            return false;
        }

        // Build RTSP playback URL
        $startStr = gmdate("Ymd\THis\Z", $start->getTimestamp());
        $endStr   = gmdate("Ymd\THis\Z", $end->getTimestamp());
        $url = "{$rtspUrl}?starttime={$startStr}&endtime={$endStr}";

        Log::channel('camera-record')->info("[RTSP DOWNLOAD START]", [
            'rtsp_url' => $url,
            'save_to' => $savePath,
            'duration' => $duration
        ]);

        // TEMPORARY RAW TS FILE
        $rawTs = dirname($savePath) . "/tester_rtsp_raw.ts";

        // Build ffmpeg command
        $cmd = [
            'ffmpeg',
            '-y',
            '-rtsp_transport',
            'tcp',
            '-i',
            $url,
            '-c',
            'copy',
            $rawTs
        ];

        if ($durationLimit > 0) {
            $cmd = [
                'ffmpeg',
                '-y',
                '-rtsp_transport',
                'tcp',
                '-i',
                $url,
                '-t',
                (string)$durationLimit,
                '-c',
                'copy',
                $rawTs
            ];
        }

        try {
            // Run ffmpeg
            $process = new Process($cmd);
            $process->setTimeout(0);
            $process->run();

            if (!$process->isSuccessful() || !file_exists($rawTs) || filesize($rawTs) < 1024) {
                Log::channel('camera-record')->error("[RTSP DOWNLOAD FAIL]", [
                    'stderr' => $process->getErrorOutput()
                ]);
                return false;
            }

            // =========================================================
            // CHECK CODEC USING FFPROBE
            // =========================================================

            // Check Video Codec
            $videoCodecProbe = new Process([
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
            ]);
            $videoCodecProbe->run();
            $videoCodec = trim($videoCodecProbe->getOutput());

            // Check Audio Codec
            $audioCodecProbe = new Process([
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
            ]);
            $audioCodecProbe->run();
            $audioCodec = trim($audioCodecProbe->getOutput());

            Log::channel('camera-record')->info("[RTSP CODEC CHECK]", [
                'video' => $videoCodec,
                'audio' => $audioCodec,
                'raw_file' => $rawTs,
                'size' => filesize($rawTs),
            ]);

            // =========================================================
            // IF CODEC OK → COPY DIRECT TO MP4
            // =========================================================
            if ($videoCodec === 'h264' && $audioCodec === 'aac') {

                $cmdCopy = [
                    'ffmpeg',
                    '-y',
                    '-i',
                    $rawTs,
                    '-c',
                    'copy',
                    '-movflags',
                    '+faststart',
                    $savePath
                ];

                $copyProcess = new Process($cmdCopy);
                $copyProcess->setTimeout(0);
                $copyProcess->run();

                @unlink($rawTs);

                if ($copyProcess->isSuccessful() && file_exists($savePath)) {
                    Log::channel('camera-record')->info("[RTSP SAVE OK - DIRECT COPY]", [
                        'file' => $savePath,
                        'size' => filesize($savePath)
                    ]);
                    return true;
                }

                Log::channel('camera-record')->error("[RTSP DIRECT COPY FAIL]", [
                    'stderr' => $copyProcess->getErrorOutput()
                ]);

                return false;
            }

            // =========================================================
            // ELSE: RE-ENCODE (H264 + AAC)
            // =========================================================
            $cmdEncode = [
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
                $savePath
            ];

            $encodeProcess = new Process($cmdEncode);
            $encodeProcess->setTimeout(0);
            $encodeProcess->run();

            @unlink($rawTs);

            if (!$encodeProcess->isSuccessful() || !file_exists($savePath)) {
                Log::channel('camera-record')->error("[RTSP ENCODE FAIL]", [
                    'stderr' => $encodeProcess->getErrorOutput()
                ]);
                return false;
            }

            Log::channel('camera-record')->info("[RTSP SAVE OK - ENCODED]", [
                'file' => $savePath,
                'size' => filesize($savePath)
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::channel('camera-record')->error("[RTSP DOWNLOAD ERROR]", [
                'error' => $e->getMessage()
            ]);
            return false;
        }
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
                        'status'  => $response->status(),
                    ]);
                    continue;
                }

                $xml = @simplexml_load_string($response->body());
                if (!$xml || !isset($xml->matchList)) continue;

                $uris = $this->extractUrisFromXml($xml, $startTs, $endTs);

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

        $cameraKey = array_key_first($allUris);
        $uris = $allUris[$cameraKey] ?? [];

        if (empty($uris)) {
            Log::channel('camera-record')->warning("[DOWNLOAD] No URIs for {$cameraKey}");
            return null;
        }

        $directSegment = collect($uris)->firstWhere('directUse', true);
        if ($directSegment) {
            $tmpDir = storage_path("app/tmp_recordings/" . uniqid("{$cameraKey}_"));
            @mkdir($tmpDir, 0777, true);
            $encodedSegments = $this->downloadAndEncodeSegments($cameraKey, [$directSegment['uri']], $tmpDir);
            return $encodedSegments[0] ?? null;
        }

        $tmpDir = storage_path("app/tmp_recordings/" . uniqid("{$cameraKey}_"));
        @mkdir($tmpDir, 0777, true);

        $urisList = array_column($uris, 'uri');
        $encodedSegments = $this->downloadAndEncodeSegments($cameraKey, $urisList, $tmpDir);

        return $this->concatEncodedSegments($cameraKey, $encodedSegments, $tmpDir);
    }

    // =========================================================
    // TRIM FINAL VIDEO
    // =========================================================
    public function trimVideo(string $inputFile, int $startSec, int $duration, string $outputFile, bool $forceEncode = false): bool
    {
        if (!file_exists($inputFile) || filesize($inputFile) < 1024) return false;

        $cmd = $this->buildTrimCommand($inputFile, $outputFile, $startSec, $duration, $forceEncode);

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
    public function extractStartTimeFromUri(string $uri): int
    {
        preg_match('/starttime=(\d{8}T\d{6})Z?/', $uri, $matches);
        if (isset($matches[1])) {
            $dt = \DateTime::createFromFormat('Ymd\THis', $matches[1], new \DateTimeZone('UTC'));
            return $dt ? $dt->getTimestamp() : 0;
        }
        return 0;
    }

    // =========================================================
    // DOWNLOAD SEGMENTS HELPERS
    // =========================================================
    private function downloadAndEncodeSegments(string $cameraKey, array $uris, string $tmpDir): array
    {
        $client = new \GuzzleHttp\Client([
            'verify' => false,
            'auth' => [$this->user, $this->pass, 'digest'],
            'timeout' => 0,
        ]);

        $downloadUrl = "https://{$this->host}/ISAPI/ContentMgmt/download";
        $encodedFiles = [];
        $maxParallel = 4;
        $processes = [];
        $seq = 1;

        foreach ($uris as $uri) {
            $rawTs = "{$tmpDir}/seg_{$seq}.ts";
            $encodedMp4 = "{$tmpDir}/seg_{$seq}.mp4";
            $xml = $this->buildDownloadXmlPayload($uri, $this->user, $this->pass);

            try {
                Log::channel('camera-record')->info("[DOWNLOAD SEGMENT] Start", [
                    'camera' => $cameraKey,
                    'segment' => $seq,
                ]);

                $client->post($downloadUrl, [
                    'headers' => ['Content-Type' => 'application/xml'],
                    'body' => $xml,
                    'sink' => $rawTs,
                ]);

                if (!file_exists($rawTs) || filesize($rawTs) < 1024) {
                    Log::channel('camera-record')->warning("[DOWNLOAD SEG FAIL] seg_{$seq}");
                    continue;
                }

                $codecCheck = new Process([
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
                ]);
                $codecCheck->run();
                $videoCodec = trim($codecCheck->getOutput());

                $audioCheck = new Process([
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
                ]);
                $audioCheck->run();
                $audioCodec = trim($audioCheck->getOutput());

                if ($videoCodec === 'h264' && $audioCodec === 'aac') {
                    $encodedFiles[] = $rawTs;
                    Log::channel('camera-record')->info("[SEG COPY OK] seg_{$seq}", ['size' => filesize($rawTs)]);
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
                    $processes[] = ['process' => $process, 'raw' => $rawTs, 'mp4' => $encodedMp4];
                }
            } catch (\Throwable $e) {
                Log::channel('camera-record')->error("[DOWNLOAD ERROR] seg_{$seq}", ['error' => $e->getMessage()]);
            }

            while (count($processes) >= $maxParallel) {
                foreach ($processes as $key => $p) {
                    if (!$p['process']->isRunning()) {
                        if ($p['process']->isSuccessful() && file_exists($p['mp4'])) {
                            $encodedFiles[] = $p['mp4'];
                            Log::channel('camera-record')->info("[SEG ENCODE OK] seg_{$seq}", ['size' => filesize($p['mp4'])]);
                        } else {
                            Log::channel('camera-record')->error("[SEG ENCODE FAIL] seg_{$seq}", [
                                'stderr' => $p['process']->getErrorOutput()
                            ]);
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
                $encodedFiles[] = $p['mp4'];
                Log::channel('camera-record')->info("[SEG ENCODE OK FINAL]", ['size' => filesize($p['mp4'])]);
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
        file_put_contents($listFile, implode("\n", array_map(fn($f) => "file '{$f}'", $encodedFiles)));
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
            if (is_file($f)) @unlink($f);
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

    // =========================================================
    // TRIM COMMAND HELPERS
    // =========================================================
    private function buildTrimCommand(string $inputFile, string $outputFile, int $startSec, int $duration, bool $forceEncode): array
    {
        return $forceEncode
            ? [
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
            ]
            : [
                'ffmpeg',
                '-y',
                '-i',
                $inputFile,
                '-ss',
                (string)$startSec,
                '-t',
                (string)$duration,
                '-c',
                'copy',
                '-movflags',
                '+faststart',
                $outputFile
            ];
    }

    // =========================================================
    // EXTRACT URIS HELPERS
    // =========================================================
    private function extractUrisFromXml(\SimpleXMLElement $xml, int $startTs, int $endTs, int $tolerance = 60): array
    {
        $uris = [];
        $coveredRanges = [];

        foreach ($xml->matchList->searchMatchItem as $item) {
            $segStart = strtotime((string)$item->timeSpan->startTime);
            $segEnd = strtotime((string)$item->timeSpan->endTime);
            $uri = (string)$item->mediaSegmentDescriptor->playbackURI;

            if (!$uri || $segEnd <= $startTs - $tolerance || $segStart >= $endTs + $tolerance) {
                continue;
            }

            preg_match('/starttime=(\d{8}T\d{6})Z?/', $uri, $s);
            preg_match('/endtime=(\d{8}T\d{6})Z?/', $uri, $e);
            if (isset($s[1], $e[1])) {
                $uriStart = max(\DateTime::createFromFormat('Ymd\THis', $s[1], new \DateTimeZone('UTC'))->getTimestamp(), $startTs);
                $uriEnd   = min(\DateTime::createFromFormat('Ymd\THis', $e[1], new \DateTimeZone('UTC'))->getTimestamp(), $endTs);

                $uri = preg_replace('/starttime=\d{8}T\d{6}/', "starttime=" . gmdate('Ymd\THis', $uriStart), $uri);
                $uri = preg_replace('/endtime=\d{8}T\d{6}/', "endtime=" . gmdate('Ymd\THis', $uriEnd), $uri);

                $recordingDuration = $endTs - $startTs;
                $uriCoverage = $uriEnd - $uriStart;
                $coverageRatio = $uriCoverage / max($recordingDuration, 1);
                $startDiff = abs($uriStart - $startTs);
                $endDiff = abs($uriEnd - $endTs);

                $directUse = (($startDiff <= $tolerance * 3 || $endDiff <= $tolerance * 3) || $coverageRatio >= 0.90);

                $uris[] = [
                    'uri' => $uri,
                    'start' => $uriStart,
                    'end' => $uriEnd,
                    'directUse' => $directUse
                ];

                $coveredRanges[] = [$uriStart, $uriEnd];

                Log::channel('camera-record')->debug("[URI RANGE DEBUG]", [
                    'start' => gmdate('Y-m-d H:i:s', $segStart),
                    'end' => gmdate('Y-m-d H:i:s', $segEnd),
                    'uriStart' => gmdate('Y-m-d H:i:s', $uriStart),
                    'uriEnd' => gmdate('Y-m-d H:i:s', $uriEnd),
                    'coverageRatio' => round($coverageRatio, 2),
                    'directUse' => $directUse
                ]);
            }
        }

        usort($uris, fn($a, $b) => $a['start'] <=> $b['start']);

        $directUri = collect($uris)->first(fn($u) => $u['directUse']);
        if ($directUri) {
            Log::channel('camera-record')->info("[URI SELECT] Direct use URI found", [
                'uri' => $directUri['uri'],
                'start' => gmdate('Y-m-d H:i:s', $directUri['start']),
                'end' => gmdate('Y-m-d H:i:s', $directUri['end'])
            ]);
            return [$directUri];
        }

        if (!empty($uris)) {
            usort($uris, fn($a, $b) => ($b['end'] - $b['start']) <=> ($a['end'] - $a['start']));
            $best = $uris[0];
            $coverageRatio = round(($best['end'] - $best['start']) / max($endTs - $startTs, 1), 2);

            Log::channel('camera-record')->info("[URI SELECT] Best coverage URI selected", [
                'uri' => $best['uri'],
                'start' => gmdate('Y-m-d H:i:s', $best['start']),
                'end' => gmdate('Y-m-d H:i:s', $best['end']),
                'coverageRatio' => $coverageRatio
            ]);

            return [$best];
        }

        Log::channel('camera-record')->warning("[URI SELECT] No URI exactly covers range, taking all available URIs", [
            'request_start' => gmdate('Y-m-d H:i:s', $startTs),
            'request_end' => gmdate('Y-m-d H:i:s', $endTs),
            'uri_count' => count($uris)
        ]);

        return $uris;
    }
}
