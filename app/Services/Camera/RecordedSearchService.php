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

        $cameraKey = array_key_first($allUris);
        $uris = $allUris[$cameraKey] ?? [];

        if (empty($uris)) {
            Log::channel('camera-record')->warning("[DOWNLOAD] No URIs for {$cameraKey}");
            return null;
        }

        $tmpDir = storage_path("app/tmp_recordings/" . uniqid("{$cameraKey}_"));
        @mkdir($tmpDir, 0777, true);

        // $rawFiles = $this->downloadSegments($cameraKey, $uris, $tmpDir);
        // if (empty($rawFiles)) return null;
        $encodedSegments = $this->downloadAndEncodeSegments($cameraKey, $uris, $tmpDir);
        if (empty($encodedSegments)) return null;

        // $concatFile = $this->concatSegments($cameraKey, $rawFiles, $tmpDir);
        // if (!$concatFile) return null;

        // return $this->encode($cameraKey, $concatFile, $tmpDir);
        return $this->concatEncodedSegments($cameraKey, $encodedSegments, $tmpDir);
    }

    // =========================================================
    // STEP 3: TRIM FINAL VIDEO
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
        // $rawFiles = [];
        $encodedFiles = [];
        $processes = [];
        $seq = 1;
        $maxParallel = 4;

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

            //     $encode = new Process([
            //         'ffmpeg',
            //         '-y',
            //         '-err_detect',
            //         'ignore_err',
            //         '-i',
            //         $rawTs,
            //         '-c:v',
            //         'libx264',
            //         '-preset',
            //         'ultrafast',
            //         '-crf',
            //         '23',
            //         '-c:a',
            //         'aac',
            //         '-b:a',
            //         '128k',
            //         '-movflags',
            //         '+faststart',
            //         $encodedMp4
            //     ]);
            //     $encode->setTimeout(0)->run();

            //     // if (file_exists($rawTs) && filesize($rawTs) > 1024) {
            //     //     $rawFiles[] = $rawTs;
            //     //     Log::channel('camera-record')->info("[DOWNLOAD SEG OK] seg_{$seq}", ['size' => filesize($rawTs)]);
            //     // } else {
            //     //     Log::channel('camera-record')->warning("[DOWNLOAD SEG FAIL] seg_{$seq}", ['size' => 0]);
            //     // }

            //     if ($encode->isSuccessful() && file_exists($encodedMp4)) {
            //         $encodedFiles[] = $encodedMp4;
            //         Log::channel('camera-record')->info("[SEG ENCODE OK] seg_{$seq}", ['size' => filesize($encodedMp4)]);
            //     } else {
            //         Log::channel('camera-record')->error("[SEG ENCODE FAIL] seg_{$seq}", [
            //             'stderr' => $encode->getErrorOutput()
            //         ]);
            //     }

            //     @unlink($rawTs);
            // } catch (\Throwable $e) {
            //     Log::channel('camera-record')->error("[DOWNLOAD ERROR] seg_{$seq}", ['error' => $e->getMessage()]);
            // }

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

        // return $rawFiles;
        return $encodedFiles;
    }

    // =========================================================
    // CONCAT SEGMENTS HELPERS
    // =========================================================
    private function concatEncodedSegments(string $cameraKey, array $encodedFiles, string $tmpDir): ?string
    {
        // array $rawFiles;
        if (empty($encodedFiles)) return null;

        $listFile = "{$tmpDir}/list.txt";
        // file_put_contents($listFile, implode("\n", array_map(fn($f) => "file '{$f}'", $rawFiles)));
        // $concatFile = "{$tmpDir}/concat_{$cameraKey}.ts";
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
            // $concatFile
            '-movflags',
            '+faststart',
            $finalFile
        ]);
        $concat->setTimeout(0)->run();

        // foreach ([$listFile, ...$rawFiles] as $f) {
        //     if (is_file($f)) @unlink($f);
        // }
        foreach ($encodedFiles as $f) {
            if (is_file($f)) @unlink($f);
        }
        @unlink($listFile);

        // if (!$concat->isSuccessful() || !file_exists($concatFile) || filesize($concatFile) < 1024) {
        //     Log::channel('camera-record')->error("[FFMPEG CONCAT FAIL]", [
        //         'error' => $concat->getErrorOutput()
        //     ]);
        //     return null;
        // }

        // Log::channel('camera-record')->info('[CONCAT OK]', [
        //     'file' => basename($concatFile),
        //     'size' => filesize($concatFile)
        // ]);

        // return $concatFile;

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
    // ENCODE SEGMENTS HELPERS
    // =========================================================
    private function encode(string $cameraKey, string $concatFile, string $tmpDir): ?string
    {
        $finalFile = "{$tmpDir}/final_{$cameraKey}.mp4";

        $encode = new Process([
            'ffmpeg',
            '-y',
            '-err_detect',
            'ignore_err',
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
        $encode->setTimeout(0)->run();

        if (!$encode->isSuccessful() || !file_exists($finalFile) || filesize($finalFile) < 1024) {
            Log::channel('camera-record')->error("[FFMPEG ENCODE FAIL]", [
                'error' => $encode->getErrorOutput()
            ]);
            return null;
        }

        @unlink($concatFile);
        Log::channel('camera-record')->info('[ENCODE OK]', [
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
    private function extractUrisFromXml(\SimpleXMLElement $xml, int $startTs, int $endTs): array
    {
        $uris = [];
        foreach ($xml->matchList->searchMatchItem as $item) {
            $segStart = strtotime((string)$item->timeSpan->startTime);
            $segEnd   = strtotime((string)$item->timeSpan->endTime);
            if ($segEnd <= $startTs || $segStart >= $endTs) continue;
            $uri = (string)$item->mediaSegmentDescriptor->playbackURI;
            if (!$uri) continue;
            preg_match('/starttime=(\d{8}T\d{6})Z?/', $uri, $s);
            preg_match('/endtime=(\d{8}T\d{6})Z?/', $uri, $e);
            if (isset($s[1], $e[1])) {
                $uriStart = max(\DateTime::createFromFormat('Ymd\THis', $s[1], new \DateTimeZone('UTC'))->getTimestamp(), $startTs);
                $uriEnd   = min(\DateTime::createFromFormat('Ymd\THis', $e[1], new \DateTimeZone('UTC'))->getTimestamp(), $endTs);
                $uri = preg_replace('/starttime=\d{8}T\d{6}/', "starttime=" . gmdate('Ymd\THis', $uriStart), $uri);
                $uri = preg_replace('/endtime=\d{8}T\d{6}/', "endtime=" . gmdate('Ymd\THis', $uriEnd), $uri);
            }
            $uris[] = $uri;
        }
        usort($uris, fn($a, $b) => $this->extractStartTimeFromUri($a) <=> $this->extractStartTimeFromUri($b));
        return $uris;
    }
}
