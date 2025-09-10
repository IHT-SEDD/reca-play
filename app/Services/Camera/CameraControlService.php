<?php

namespace App\Services\Camera;

use App\Models\Master\Api;
use App\Models\Master\Camera;
use App\Models\Master\Field;
use App\Models\Master\Nvr;
use App\Models\Master\Port;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CameraControlService
{
    protected string $host;
    protected string $user;
    protected string $pass;
    protected int $manualChannel;

    public function __construct()
    {
        $this->host = rtrim("https://192.168.1.26:443", '/');
        $this->user = "admin";
        $this->pass = "IsolaNo1";
        $this->manualChannel = 301;

        if (empty($this->host)) {
            throw new \RuntimeException('HIKVISION_HOST not configured');
        }
    }

    public function startRecording(): bool
    {
        $url = "{$this->host}/ISAPI/ContentMgmt/record/control/manual/start/tracks/{$this->manualChannel}";

        // For debugging: uncomment the following line to see the URL being hit
        // dd($url);

        try {
            $res = Http::withDigestAuth($this->user, $this->pass)
                ->withoutVerifying()
                ->put($url);

            // For debugging: uncomment the following line to see the full response from the camera
            // dd($res);

            return $res->successful();
        } catch (\Throwable $e) {
            Log::error("Failed to start recording: " . $e->getMessage(), ['exception' => $e]);
            return false;
        }
    }

    public function stopRecording(): bool
    {
        $url = "{$this->host}/ISAPI/ContentMgmt/record/control/manual/stop/tracks/{$this->manualChannel}";
        $res = Http::withDigestAuth($this->user, $this->pass)
            ->withoutVerifying()
            ->put($url);

        return $res->successful();
    }

    // ========== Get host and port data ==========
    private function getHostAndPort(): array
    {
        $urlStart = Api::where('name', 'ISAPI - Start Manual Recording')->value('url');
        $urlStop = Api::where('name', 'ISAPI - Stop Manual Recording')->value('url');

        $portHttps = Port::where('name', 'HTTPS')->value('port_number');

        if (!$urlStart) {
            Log::channel('camera-control')->error("URL for start manual recording not configured");
            throw new \RuntimeException('URL for start manual recording not configured');
        }

        if (!$urlStop) {
            Log::channel('camera-control')->error("URL for stop manual recording not configured");
            throw new \RuntimeException('URL for stop manual recording not configured');
        }

        if (!$portHttps) {
            Log::channel('camera-control')->error("Port HTTPS not configured");
            throw new \RuntimeException('Port HTTPS not configured');
        }

        return [
            'start' => $urlStart,
            'stop' => $urlStop,
            'portHttps' => $portHttps,
        ];
    }

    // ========== Get nvr data ==========
    private function getNvrData($fieldId): array
    {
        $fieldData = Field::findOrFail($fieldId);

        $cameraIds = $fieldData->value('camera_id');
        $cameraChannels = Camera::whereBetween('id', $cameraIds)->value('channel');

        $nvrId = $fieldData->Camera->Field->id;
        $nvrUrl = Nvr::where('id', $nvrId)->value('ip_address');

        if (!$nvrUrl) {
            Log::channel('camera-control')->error("NVR URL not found");
            throw new \RuntimeException('NVR URL not found');
        }

        if (!$cameraChannels) {
            Log::channel('camera-control')->error("Camera channels not found");
            throw new \RuntimeException('Camera channels not found');
        }

        return [
            'nvrUrl' => $nvrUrl,
            'cameraChannel' => $cameraChannels,
        ];
    }
}
