<?php

namespace App\Enums;

enum RecordingLogStatus: string
{
    case Stopped = 'stopped';
    case RecordStart = 'record_start';

    public function label(): string
    {
        return match ($this) {
            self::Stopped => 'Stopped',
            self::RecordStart => 'Record Start',
        };
    }

    public function isStop(): bool
    {
        return $this === self::Stopped;
    }
    public function isStart(): bool
    {
        return $this === self::RecordStart;
    }
}
