<?php

namespace App\Enums;

enum SelfieStatus: string
{
    case Capturing = 'capturing';
    case Processing = 'processing';
    case Done = 'done';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Capturing => 'Capturing',
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
