<?php

namespace App\Services\Camera;

use App\Enums\MasterStatus;
use App\Models\Master\Camera;
use App\Models\Master\Nvr;
use Illuminate\Support\Facades\Log;

class LivePreviewService
{
 protected $mediamtxHost;

 public function __construct()
 {
  $this->mediamtxHost = config('services.mediamtx.host', '127.0.0.1:8889');
 }

 public function getLivePreviewUrl(int $fieldId): ?string
 {
  $camera = Camera::where('field_id', $fieldId)
   ->where('is_active', MasterStatus::Active)
   ->orderBy('code', 'asc')
   ->first();

  return $camera ? $this->buildUrl($camera, $fieldId) : null;
 }

 public function getLivePreviewUrlByCode(int $fieldId, string $cameraCode): ?string
 {
  $camera = Camera::where('field_id', $fieldId)
   ->where('code', $cameraCode)
   ->where('is_active', MasterStatus::Active)
   ->first();

  if (!$camera) {
   Log::channel('camera-control')->warning(
    "[Live Preview] Camera {$cameraCode} in field {$fieldId} is inactive, fallback to another camera."
   );

   $camera = Camera::where('field_id', $fieldId)
    ->where('is_active', MasterStatus::Active)
    ->orderBy('code', 'asc')
    ->first();
  }

  return $camera ? $this->buildUrl($camera, $fieldId) : null;
 }

 private function buildUrl(Camera $camera, int $fieldId): ?string
 {
  $nvr = Nvr::find($camera->nvr_id);
  if (!$nvr) {
   Log::channel('camera-control')
    ->warning("[Live Preview] NVR in field {$fieldId} Not Found!");
   return null;
  }

  $cameraPath = $camera->code;
  Log::channel('camera-control')->info("[Live Preview] Camera success integrated :", [
   'cameraPath' => $cameraPath,
  ]);

  $whepUrl = sprintf(
   "https://%s/%s/whep",
   $this->mediamtxHost,
   $cameraPath
  );

  Log::channel('camera-control')->info("[Live Preview] WHEP URL created successfully => {$whepUrl}");

  return $whepUrl;
 }
}
