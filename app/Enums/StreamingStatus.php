<?php

namespace App\Enums;

enum StreamingStatus: string
{
    case Streaming = 'streaming';
    case Processing = 'processing';
    case Done = 'done';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Streaming => 'Streaming',
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
