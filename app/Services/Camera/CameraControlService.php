<?php

namespace App\Services\Camera;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CameraControlService
{
    private $host;
    private $manualChannel;
    private $user;
    private $pass;
    private $cameras = [];

    protected $prepareData;

    public function __construct(PrepareDataService $prepareData)
    {
        $this->prepareData = $prepareData;
    }

    // ========== Initialize data ==========
    public function initialize($fieldId): void
    {
        try {
            $data = $this->prepareData->prepare($fieldId);

            $this->host = $data['host'];
            $this->manualChannel = $data['manualChannel'];
            $this->user = $data['user'];
            $this->pass = $data['pass'];
            $this->cameras = $data['cameras'];

            Log::channel('camera-control')->info("[CAM CONTROL] Camera initialized", [
                'host' => $this->host,
                'channel' => $this->manualChannel,
                'cameras' => $this->cameras,
            ]);
        } catch (\Throwable $e) {
            Log::channel('camera-control')->error("[CAM CONTROL] Failed to initialize camera: " . $e->getMessage(), [
                'exception' => $e,
            ]);
            throw $e;
        }
    }

    // ========== Start recording function ==========
    public function startRecording(): bool
    {
        $allSuccess = true;

        foreach ($this->manualChannel as $channel) {
            $url = "https://{$this->host}/ISAPI/ContentMgmt/record/control/manual/start/tracks/{$channel}";

            try {
                $res = Http::withDigestAuth($this->user, $this->pass)
                    ->withoutVerifying()
                    ->put($url);

                $success = $res->successful();
                Log::channel('camera-control')->info("[CAM CONTROL] Start recording on channel {$channel}: " . ($success ? 'OK' : 'FAILED') . " w/ data : {$res}");

                if (!$success) {
                    $allSuccess = false;
                }
            } catch (\Throwable $e) {
                Log::channel('camera-control')->error("[CAM CONTROL] Failed to start recording on channel {$channel}: " . $e->getMessage(), [
                    'exception' => $e,
                ]);
                $allSuccess = false;
            }
        }

        return $allSuccess;
    }

    // ========== Stop recording function ==========
    public function stopRecording(): bool
    {
        $allSuccess = true;

        foreach ($this->manualChannel as $channel) {
            $url = "https://{$this->host}/ISAPI/ContentMgmt/record/control/manual/stop/tracks/{$channel}";

            try {
                $res = Http::withDigestAuth($this->user, $this->pass)
                    ->withoutVerifying()
                    ->put($url);

                $success = $res->successful();
                Log::channel('camera-control')->info("[CAM CONTROL] Stop recording on channel {$channel}: " . ($success ? 'OK' : 'FAILED') . " w/ data : {$res}");

                if (!$success) {
                    $allSuccess = false;
                }
            } catch (\Throwable $e) {
                Log::channel('camera-control')->error("[CAM CONTROL] Failed to stop recording on channel {$channel}: " . $e->getMessage(), [
                    'exception' => $e,
                ]);
                $allSuccess = false;
            }
        }

        return $allSuccess;
    }
}
