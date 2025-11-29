<?php

namespace App\Enums;

enum SessionCodeStatus: string
{
    case Active = 'active';
    case RecordStart = 'record_start';
    case StreamStart = 'stream_start';
    case InUse = 'in_use';
    case Done = 'done';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::RecordStart => 'Record Start',
            self::StreamStart => 'Stream Start',
            self::InUse => 'In Use',
            self::Done => 'Done',
            self::Expired => 'Expired',
        };
    }

    public function isActive(): bool
    {
        return $this === self::Active;
    }

    public function isExpired(): bool
    {
        return $this === self::Expired;
    }
}
