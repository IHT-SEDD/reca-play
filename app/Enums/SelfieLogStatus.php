<?php

namespace App\Enums;

enum SelfieLogStatus: string
{
    case Stopped = 'stopped';
    case CaptureStart = 'capture_start';

    public function label(): string
    {
        return match ($this) {
            self::Stopped => 'Stopped',
            self::CaptureStart => 'Capture Start',
        };
    }

    public function isStop(): bool
    {
        return $this === self::Stopped;
    }
    public function isStart(): bool
    {
        return $this === self::CaptureStart;
    }
}
