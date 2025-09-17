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

    private int $downloadRetries = 3;

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
        $playbackUris = [];
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
                    'Content-Type' => 'application/xml',
                    'Accept' => '*/*'
                ])->withBody($xmlPayload, 'application/xml')
                    ->post("https://{$this->host}/ISAPI/ContentMgmt/search");

                /** Uncomment this log for debugging the response XML ISAPI Search video */
                // Log::channel('camera-record')->info("[RECORD SEARCH] Response XML for channel {$channel}", [
                //     'payload' => $xmlPayload,
                //     'status' => $response->status(),
                //     'body' => $response->body()
                // ]);

                if (!$response->successful()) {
                    continue;
                }

                $xml = @simplexml_load_string($response->body());
                if (!$xml || !isset($xml->matchList)) {
                    continue;
                }

                $xml->registerXPathNamespace('ns', 'http://www.hikvision.com/ver20/XMLSchema');

                $uris = collect($xml->matchList->searchMatchItem ?? [])
                    ->map(fn($item) => (string) $item->mediaSegmentDescriptor->playbackURI)
                    ->filter()
                    ->values()
                    ->toArray();

                if (!empty($uris)) {
                    $playbackUris = array_merge($playbackUris, $uris);
                    Log::channel('camera-record')->info("[RECORD SEARCH] Found URIs", [
                        'channel' => $channel,
                        'uris' => $uris
                    ]);
                }
            } catch (\Throwable $e) {
                Log::channel('camera-record')->error("[RECORD SEARCH] Channel {$channel} search error: " . $e->getMessage());
            }
        }

        return $playbackUris;
    }

    // ==================== Download videos to storage ====================
    public function downloadByPlaybackUris(array $playbackUris, int $fieldId, int $userId, string $videoName): array
    {
        $downloadUrl = "https://{$this->host}/ISAPI/ContentMgmt/download";
        $savedFiles = [];
        $date = now()->format('dmy');
        $sequence = 1;

        $client = new \GuzzleHttp\Client([
            'verify' => false,
            'auth' => [$this->user, $this->pass, 'digest'],
            'timeout' => 0,
        ]);

        foreach ($playbackUris as $playbackURI) {
            $xmlBody = $this->buildDownloadXmlPayload($playbackURI, $this->user, $this->pass);

            $success = false;
            for ($attempt = 1; $attempt <= $this->downloadRetries; $attempt++) {
                try {
                    $filename = sprintf(
                        '%s_%s_%d%d%d.mp4',
                        $videoName,
                        $date,
                        $fieldId,
                        $userId,
                        $sequence
                    );

                    // $fullPath = storage_path("app/public/recordings/" . $filename);
                    $videoPath = storage_path("app/public/recordings/" . $filename);
                    @mkdir(dirname($videoPath), 0777, true);

                    Log::channel('camera-record')->info("[RECORD DOWNLOAD] Request XML", [
                        'playbackURI' => $playbackURI,
                        'xmlBody' => $xmlBody
                    ]);

                    $response = $client->post($downloadUrl, [
                        'headers' => [
                            'Content-Type' => 'application/xml',
                            'Accept' => '*/*'
                        ],
                        'body' => $xmlBody,
                        'sink' => $videoPath,
                    ]);

                    Log::channel('camera-record')->info("[RECORD DOWNLOAD] Download response", [
                        'status' => $response->getStatusCode(),
                        'reason' => $response->getReasonPhrase()
                    ]);

                    $thumbnailDir = storage_path('app/public/thumbnails');
                    @mkdir($thumbnailDir, 0777, true);
                    $thumbnailPath = $thumbnailDir . '/' . pathinfo($filename, PATHINFO_FILENAME) . '_thumb.jpg';

                    $this->generateThumbnail($videoPath, $thumbnailPath);

                    $savedFiles[] = [
                        'path' => 'recordings/' . $filename,
                        'filename' => $filename,
                        'size' => filesize($videoPath),
                        'thumbnail' => 'thumbnails/' . pathinfo($filename, PATHINFO_FILENAME) . '_thumb.jpg'
                    ];

                    Log::channel('camera-record')->info("[RECORD DOWNLOAD] Video & thumbnail saved", [
                        'video' => $videoPath,
                        'thumbnail' => $thumbnailPath
                    ]);

                    $success = true;
                    break;
                } catch (\Throwable $e) {
                    Log::channel('camera-record')->error("[RECORD DOWNLOAD] Error attempt #{$attempt}", [
                        'playbackURI' => $playbackURI,
                        'exception' => $e->getMessage()
                    ]);
                    sleep(2);
                }
            }

            if (!$success) {
                Log::channel('camera-record')->error("[RECORD DOWNLOAD] Failed after {$this->downloadRetries} attempts", [
                    'playbackURI' => $playbackURI
                ]);
            }

            $sequence++;
        }

        return $savedFiles;
    }

    public function generateThumbnail(string $videoPath, string $thumbnailPath): void
    {
        @mkdir(dirname($thumbnailPath), 0777, true);

        $process = new Process([
            'ffmpeg',
            '-y',
            '-i',
            $videoPath,
            '-ss',
            '00:00:10',
            '-vframes',
            '1',
            $thumbnailPath
        ]);

        $process->run();

        if (!$process->isSuccessful()) {
            Log::channel('camera-record')->error("[THUMBNAIL GENERATE] Failed : ", [$process]);
            throw new ProcessFailedException($process);
        }
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
