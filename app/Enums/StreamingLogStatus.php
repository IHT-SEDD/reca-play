<?php

namespace App\Enums;

enum StreamingLogStatus: string
{
    case Stopped = 'stopped';
    case StreamStart = 'stream_start';

    public function label(): string
    {
        return match ($this) {
            self::Stopped => 'Stopped',
            self::StreamStart => 'Stream Start',
        };
    }

    public function isStop(): bool
    {
        return $this === self::Stopped;
    }
    public function isStart(): bool
    {
        return $this === self::StreamStart;
    }
}
