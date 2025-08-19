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
        $this->host = rtrim("https://192.168.1.6:443", '/');
        $this->user = "admin";
        $this->pass = "IsolaNo1";
        $this->manualChannel = 101;

        if (empty($this->host)) {
            throw new \RuntimeException('HIKVISION_HOST not configured');
        }
    }

    public function startRecording(): bool
    {
        $url = "{$this->host}/ISAPI/ContentMgmt/record/control/manual/start/tracks/{$this->manualChannel}";
        // dd($url);
        $res = Http::withDigestAuth($this->user, $this->pass)
            ->withoutVerifying()
            ->put($url);

        return $res->successful();
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
