<?php

namespace App\Services\Camera;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class DownloadVideoService
{
  private string $host;
  private string $user;
  private string $pass;

  public function __construct(string $host = '', string $user = '', string $pass = '')
  {
    $this->host = $host;
    $this->user = $user;
    $this->pass = $pass;
  }

  public function setConnection(string $host, string $user, string $pass): void
  {
    $this->host = $host;
    $this->user = $user;
    $this->pass = $pass;
  }

  // =========================================================
  // 1. DOWNLOAD VIA ISAPI
  // =========================================================
  public function downloadViaISAPI(string $uri, string $rawTsPath): bool
  {
    $client = new \GuzzleHttp\Client([
      'verify' => false,
      'auth' => [$this->user, $this->pass, 'digest'],
      'timeout' => 0,
    ]);

    $downloadUrl = "https://{$this->host}/ISAPI/ContentMgmt/download";
    $xml = $this->buildDownloadXmlPayload($uri);

    try {
      Log::channel('camera-record')->info("[ISAPI DOWNLOAD] Start", [
        'uri' => $uri,
        'output' => $rawTsPath
      ]);

      $client->post($downloadUrl, [
        'headers' => ['Content-Type' => 'application/xml'],
        'body' => $xml,
        'sink' => $rawTsPath,
      ]);

      if (!file_exists($rawTsPath) || filesize($rawTsPath) < 1024) {
        Log::channel('camera-record')->warning("[ISAPI DOWNLOAD FAIL]", [
          'file' => $rawTsPath
        ]);
        return false;
      }

      Log::channel('camera-record')->info("[ISAPI DOWNLOAD OK]", [
        'file' => $rawTsPath,
        'size' => filesize($rawTsPath)
      ]);

      return true;
    } catch (\Throwable $e) {
      Log::channel('camera-record')->error("[ISAPI DOWNLOAD ERROR]", [
        'uri' => $uri,
        'error' => $e->getMessage()
      ]);
      return false;
    }
  }

  // =========================================================
  // Helper: Build XML payload for ISAPI download
  // =========================================================
  private function buildDownloadXmlPayload(string $uri): string
  {
    return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<downloadRequest version="1.0" xmlns="http://www.isapi.org/ver20/XMLSchema">
    <playbackURI>{$uri}</playbackURI>
    <userName>{$this->user}</userName>
    <password>{$this->pass}</password>
</downloadRequest>
XML;
  }
}
