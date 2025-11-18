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

  // =========================================================
  // GET LIVE PREVIEW (AUTO CAMERA)
  // =========================================================
  public function getLivePreviewUrl(int $fieldId): ?string
  {
    $camera = Camera::where('field_id', $fieldId)
      ->where('is_active', MasterStatus::Active)
      ->orderBy('code', 'asc')
      ->first();

    if ($camera) {
      Log::channel('camera-control')->info("[Live Preview] Camera found for field {$fieldId}", [
        'camera_code' => $camera->code,
        'nvr_id' => $camera->nvr_id,
      ]);
    }

    return $camera ? $this->buildUrl($camera, $fieldId) : null;
  }

  // =========================================================
  // GET LIVE PREVIEW (BY CODE)
  // =========================================================
  public function getLivePreviewUrlByCode(int $fieldId, string $cameraCode): ?string
  {
    $camera = Camera::where('field_id', $fieldId)
      ->where('code', $cameraCode)
      ->where('is_active', MasterStatus::Active)
      ->first();

    if ($camera) {
      Log::channel('camera-control')->info("[Live Preview] Requested camera found", [
        'field_id' => $fieldId,
        'requested_camera_code' => $cameraCode,
        'using_camera_code' => $camera->code,
        'nvr_id' => $camera->nvr_id,
      ]);
    }

    if (!$camera) {
      Log::channel('camera-control')->warning(
        "[Live Preview] Camera {$cameraCode} in field {$fieldId} is inactive, fallback to another active camera."
      );

      $camera = Camera::where('field_id', $fieldId)
        ->where('is_active', MasterStatus::Active)
        ->orderBy('code', 'asc')
        ->first();

      if ($camera) {
        Log::channel('camera-control')->info("[Live Preview] Fallback camera selected", [
          'fallback_camera_code' => $camera->code,
          'nvr_id' => $camera->nvr_id,
        ]);
      }
    }

    return $camera ? $this->buildUrl($camera, $fieldId) : null;
  }

  // =========================================================
  // BUILD LIVE PREVIEW URL
  // =========================================================
  private function buildUrl(Camera $camera, int $fieldId): ?string
  {
    $nvr = Nvr::find($camera->nvr_id);
    if (!$nvr) {
      Log::channel('camera-control')->warning("[Live Preview] NVR for field {$fieldId} not found!");
      return null;
    }

    $cameraPath = $camera->code;

    Log::channel('camera-control')->info("[Live Preview] Camera integrated successfully", [
      'cameraPath' => $cameraPath,
      'nvr_id' => $camera->nvr_id,
    ]);

    $whepUrl = sprintf(
      "https://%s/%s/whep",
      $this->mediamtxHost,
      $cameraPath
    );

    Log::channel('camera-control')->info("[Live Preview] WHEP URL created successfully", [
      'url' => $whepUrl,
    ]);

    return $whepUrl;
  }
}
