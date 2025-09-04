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
    $this->host = rtrim("https://192.168.1.5:443", '/');
    $this->user = "admin";
    $this->pass = "IsolaNo1";
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
      if ($uri) return $uri;

      Log::warning("Search attempt #$i: no recording found, retrying...");
      sleep(3);
    }

    return null;
  }

  protected function searchRecording(): ?string
  {
    $startSession = session('camera_start_time');
    $start = $startSession
      ? gmdate('Y-m-d\TH:i:s\Z', strtotime($startSession . ' -1 minute'))
      : gmdate('Y-m-d\T00:00:00\Z');
    $end = gmdate('Y-m-d\TH:i:s\Z');

    $searchUrl = "{$this->host}/ISAPI/ContentMgmt/search";

    $xmlBody = <<<XML
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
              <metadataDescriptor>/recordType.meta.std-cgi.com/</metadataDescriptor>
          </metadataList>
      </CMSearchDescription>
      XML;

    $response = Http::withDigestAuth($this->user, $this->pass)
      ->withoutVerifying()
      ->withHeaders([
        'Content-Type' => 'application/xml',
        'Accept' => '*/*'
      ])
      ->post($searchUrl, $xmlBody);

    if ($response->failed()) {
      Log::error("Hikvision search failed", ['status' => $response->status()]);
      return null;
    }

    $xml = @simplexml_load_string($response->body());
    if (!$xml) return null;

    $xml->registerXPathNamespace('ns', 'http://www.hikvision.com/ver20/XMLSchema');

    $numMatches = (int) ($xml->xpath('//ns:numOfMatches')[0] ?? 0);
    if ($numMatches < 1) return null;

    $playbackUri = $xml->xpath('//ns:playbackURI')[0] ?? null;
    return $playbackUri ? html_entity_decode((string)$playbackUri) : null;
  }

  /**
   * Download rekaman dari playbackURI
   * Mengembalikan path relatif di storage/public/recordings
   */
  public function downloadByPlaybackUri(string $playbackUri): ?string
  {
    $downloadUrl = "{$this->host}/ISAPI/ContentMgmt/download";

    $xmlBody = <<<XML
      <downloadRequest version="1.0" xmlns="http://www.isapi.org/ver20/XMLSchema">
          <playbackURI>{$playbackUri}</playbackURI>
          <userName>{$this->user}</userName>
          <password>{$this->pass}</password>
      </downloadRequest>
      XML;

    for ($attempt = 1; $attempt <= 3; $attempt++) {
      $response = Http::withHeaders([
        'Content-Type' => 'application/xml',
        'Accept' => '*/*'
      ])->withoutVerifying()
        ->post($downloadUrl, $xmlBody);

      if (!$response->failed() && $response->body()) {
        $filename = 'recording_' . now()->format('Ymd_His') . '.mp4';
        Storage::disk('public')->put('recordings/' . $filename, $response->body());
        return 'recordings/' . $filename;
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
    $filename = 'recording_' . now()->format('Ymd_His') . '.mp4';
    $outputPath = storage_path('app/public/recordings/' . $filename);

    $cmd = sprintf(
      'ffmpeg -i "%s" -c copy "%s" -y',
      escapeshellarg($rtspUrl),
      escapeshellarg($outputPath)
    );

    exec($cmd, $output, $returnVar);

    return $returnVar === 0 ? 'recordings/' . $filename : null;
  }
}
