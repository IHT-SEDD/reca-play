<?php

namespace App\Services\Camera;

use App\Models\Master\Api;
use App\Models\Master\Camera;
use App\Models\Master\Field;
use App\Models\Master\Nvr;
use App\Models\Master\Port;
use Illuminate\Support\Facades\Log;

class PrepareDataService
{
 public function prepare(int $fieldId): array
 {
  $hostPortData = $this->getHostAndPort();
  $nvrData = $this->getNvrData($fieldId);

  $host = $nvrData['nvrUrl'] . ':' . $hostPortData['portHttps'];

  return [
   'host' => $host,
   'manualChannel' => $nvrData['cameraChannel'],
   'user' => $nvrData['username'],
   'pass' => $nvrData['password'],
   'cameras' => $nvrData['cameras'],
  ];
 }

 private function getHostAndPort(): array
 {
  $urlStart = Api::where('name', 'LIKE', '%Start Manual Recording%')->value('url');
  $urlStop = Api::where('name', 'LIKE', '%Stop Manual Recording%')->value('url');
  $portHttps = Port::where('name', 'HTTPS')->value('port_number');

  if (!$urlStart || !$urlStop || !$portHttps) {
   Log::channel('camera-control')->error("Config host/port tidak lengkap");
   throw new \RuntimeException('Config host/port tidak lengkap');
  }

  return [
   'start' => $urlStart,
   'stop' => $urlStop,
   'portHttps' => $portHttps,
  ];
 }

 private function getNvrData(int $fieldId): array
 {
  $fieldData = Field::findOrFail($fieldId);
  $cameras = Camera::where('field_id', $fieldId)->get(['id', 'code', 'name', 'channel', 'nvr_id']);

  if ($cameras->isEmpty()) {
   Log::channel('camera-control')->error("No cameras found for field_id {$fieldId}");
   throw new \RuntimeException("No cameras found for this field");
  }

  $cameraChannels = $cameras->pluck('channel')->toArray();
  $cameraDetails  = $cameras->map(fn($cam) => [
   'id' => $cam->id,
   'code' => $cam->code,
   'name' => $cam->name,
  ])->toArray();

  $nvrId = $cameras->first()->nvr_id;
  $nvrData = Nvr::where('id', $nvrId)->first();

  if (!$nvrData) {
   Log::channel('camera-control')->error("NVR Data not found for field_id {$fieldId}");
   throw new \RuntimeException('NVR Data not found');
  }

  if (!$nvrData->username || !$nvrData->password) {
   Log::channel('camera-control')->error("NVR Username/Password not found for field_id {$fieldId}");
   throw new \RuntimeException('NVR Username/Password not found');
  }

  return [
   'nvrUrl' => $nvrData->ip_address,
   'cameraChannel' => $cameraChannels,
   'username' => $nvrData->username,
   'password' => $nvrData->password,
   'cameras' => $cameraDetails,
  ];
 }
}
