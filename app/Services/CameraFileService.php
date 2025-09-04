<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CameraFileService
{
  protected string $host;
  protected string $user;
  protected string $pass;
  protected int $searchTrack;

  public function __construct()
  {
    $this->host        = rtrim("https://192.168.1.26:443", '/');
    $this->user        = "admin";
    $this->pass        = "IsolaNo1";
    $this->searchTrack = 301;

    if (empty($this->host)) {
      throw new \RuntimeException('HIKVISION_HOST not configured');
    }

    Storage::disk('public')->makeDirectory('recordings');
    Storage::disk('public')->makeDirectory('thumbnails');
  }

  /**
   * Ambil playbackURI rekaman terbaru
   */
  public function getLatestPlaybackUri(): ?string
  {
    for ($i = 1; $i <= 3; $i++) {
      $uri = $this->searchRecording();
      if ($uri)
        return $uri;

      Log::warning("Search attempt #$i: no recording found, retrying...");
      sleep(3);
    }

    return null;
  }

  protected function searchRecording2(): ?string
  {
    $startSession = session('camera_start_time');
    $start        = $startSession
      ? gmdate('Y-m-d\TH:i:s\Z', strtotime($startSession.' -1 minute +7 hours'))
      : gmdate('Y-m-d\T00:00:00\Z', strtotime('now +7 hours'));
    $end          = gmdate('Y-m-d\TH:i:s\Z', strtotime('now +7 hours'));

    $searchUrl = "{$this->host}/ISAPI/ContentMgmt/search";
    Log::info("Hikvision start time: ".$start);
    Log::info("Hikvision end time: ".$end);
    $xmlBody = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<CMSearchDescription>
    <searchID>12345678-ABCD-1234-ABCD-1234567890AB</searchID>
    <trackIDList>
        <trackID>{$this->searchTrack}</trackID>
    </trackIDList>
    <timeSpanList>
        <timeSpan>
            <startTime>{$start}</startTime>
            <endTime>{$end}</endTime>
        </timeSpan>
    </timeSpanList>
    <maxResults>40</maxResults>
    <searchResultPosition>0</searchResultPosition>
    <metadataList>
        <metadataDescriptor>recordType.meta.std-cgi.com</metadataDescriptor>
    </metadataList>
</CMSearchDescription>
XML;

    Log::info("Hikvision search URL: ".$searchUrl);
    Log::info("Hikvision search request XML: ".$xmlBody);

    $response = Http::withOptions([
      'verify' => false, // bypass self-signed SSL
      'auth' => ['admin', 'IsolaNo1', 'digest'], // <-- Digest Auth
    ])
      ->withHeaders([
        'Content-Type' => 'application/xml',
        'Accept' => '*/*'
      ])
      ->post($searchUrl, $xmlBody);

    Log::info("Hikvision search response status: ".$response->status());
    Log::info("Hikvision search response body: ".$response->body());

    if ($response->failed()) {
      Log::error("Hikvision search failed", ['status' => $response->status(), 'response' => $response->body()]);
      return null;
    }

    $xml = @simplexml_load_string($response->body());
    if (! $xml) {
      Log::error("Failed to parse XML response from Hikvision search.");
      return null;
    }

    $xml->registerXPathNamespace('ns', 'http://www.hikvision.com/ver20/XMLSchema');

    $numMatches = (int) ($xml->xpath('//ns:numOfMatches')[0] ?? 0);
    Log::info("Hikvision search numMatches: ".$numMatches);

    if ($numMatches < 1)
      return null;

    $playbackUri = $xml->xpath('//ns:playbackURI')[0] ?? null;
    Log::info("Hikvision search playbackUri: ".($playbackUri ? html_entity_decode((string) $playbackUri) : 'null'));

    return $playbackUri ? html_entity_decode((string) $playbackUri) : null;
  }

  protected function searchRecording()
  {
    $startSession = session('camera_start_time');
    $start        = $startSession
      ? gmdate('Y-m-d\TH:i:s\Z', strtotime($startSession.' -1 minute +7 hours'))
      : gmdate('Y-m-d\T00:00:00\Z', strtotime('now +7 hours'));
    $end          = gmdate('Y-m-d\TH:i:s\Z', strtotime('now +7 hours'));

    $searchUrl = "{$this->host}/ISAPI/ContentMgmt/search";
    Log::info("Hikvision start time: ".$start);
    Log::info("Hikvision end time: ".$end);
    $xmlPayload = '<?xml version="1.0" encoding="utf-8"?>
<CMSearchDescription>
    <searchID>12345678-ABCD-1234-ABCD-1234567890AB</searchID>
    <trackIDList>
        <trackID>301</trackID>
    </trackIDList>
    <timeSpanList>
        <timeSpan>
            <startTime>'.$start.'</startTime>
            <endTime>'.$end.'</endTime>
        </timeSpan>
    </timeSpanList>
    <maxResults>40</maxResults>
    <searchResultPosition>0</searchResultPosition>
    <metadataList>
        <metadataDescriptor>//recordType.meta.std-cgi.com</metadataDescriptor>
    </metadataList>
</CMSearchDescription>';

    try {
      $response = Http::withOptions([
        'verify' => false, // Only if using self-signed certificates
        'timeout' => 30,
        'auth' => ['admin', 'IsolaNo1', 'digest']
      ])
        ->withHeaders([
          'Content-Type' => 'application/xml'
        ])
        ->withBody($xmlPayload, 'application/xml')
        ->post('https://192.168.1.26/ISAPI/ContentMgmt/search');

      // dd($response->body());
      if ($response->successful()) {
        $xml         = @simplexml_load_string($response->body());
        $playbackUri = collect($xml->matchList->searchMatchItem)
          ->map(function ($item) {
            return (string) $item->mediaSegmentDescriptor->playbackURI;
          })
          ->first();
        // dd($playbackUris[0]);
        Log::info("PLAYBACK".$playbackUri);
        // dd($xml->xpath('//ns:playbackURI')[0]);
        // $playbackUri = $xml->xpath('//ns:playbackURI')[0] ?? null;
        // $playbackUri = $playbackUris;
        // dd($playbackUris[0]);
        // Log::info("Hikvision search playbackUri: ".($playbackUris[0]["searchMatchItem"]));
        // Log::info("Hikvision search playbackUri: ".($playbackUri ? html_entity_decode((string) $playbackUri) : 'null'));

        return $playbackUri;
        // return [
        //   'success' => true,
        //   'data' => $response->body(),
        //   'status' => $response->status()
        // ];
      }

      return null;

      // return [
      //   'success' => false,
      //   'error' => 'Request failed with status: '.$response->status(),
      //   'body' => $response->body()
      // ];

    } catch (\Exception $e) {
      Log::error($e->getMessage());
      return null;
    }
  }

  /**
   * Download rekaman dari playbackURI
   * Mengembalikan path relatif di storage/public/recordings
   */
  public function downloadByPlaybackUri(string $playbackUri): ?string
  {
    $downloadUrl = "https://192.168.1.26:443/ISAPI/ContentMgmt/download";

    $xmlBody = '<?xml version="1.0" encoding="utf-8"?>
    <downloadRequest version="1.0" xmlns="http://www.isapi.org/ver20/XMLSchema">
          <playbackURI>'.$playbackUri.'</playbackURI>
      </downloadRequest>';

    for ($attempt = 1; $attempt <= 3; $attempt++) {
      $response = Http::withHeaders([
        'Content-Type' => 'application/xml',
        'Accept' => '*/*'
      ])->withOptions([
            'verify' => false, // Only if using self-signed certificates
            'timeout' => 30,
            'auth' => ['admin', 'IsolaNo1', 'digest']
          ])
        ->withBody($xmlBody, 'application/xml')
        ->post($downloadUrl);

      if (! $response->failed() && $response->body()) {
        $filename = 'recording_'.now()->format('Ymd_His').'.mp4';
        Storage::disk('public')->put('recordings/'.$filename, $response->body());
        return 'recordings/'.$filename;
      }

      Log::warning("Download attempt #$attempt failed, retrying...");
      sleep(2);
    }

    Log::error("Failed to download recording after 3 attempts", ['uri' => $playbackUri]);
    return null;
  }

  /**
   * Fallback download via RTSP (optional, jika ISAPI gagal)
   */
  public function downloadByRtsp(string $rtspUrl): ?string
  {
    $filename   = 'recording_'.now()->format('Ymd_His').'.mp4';
    $outputPath = storage_path('app/public/recordings/'.$filename);

    $cmd = sprintf(
      'ffmpeg -i "%s" -c copy "%s" -y',
      escapeshellarg($rtspUrl),
      escapeshellarg($outputPath)
    );

    exec($cmd, $output, $returnVar);

    return $returnVar === 0 ? 'recordings/'.$filename : null;
  }
}
