<?php

namespace App\Enums;

enum RecordingStatus: string
{
    case Recording = 'recording';
    case Processing = 'processing';
    case Done = 'done';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Recording => 'Recording',
            self::Processing => 'Processing',
            self::Done => 'Done',
            self::Failed => 'Failed',
        };
    }

    public function isDone(): bool
    {
        return $this === self::Done;
    }
    public function isFailed(): bool
    {
        return $this === self::Failed;
    }
}
