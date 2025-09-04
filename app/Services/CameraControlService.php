<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

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
}
