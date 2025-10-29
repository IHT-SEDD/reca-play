<?php

namespace App\Enums;

enum SessionCodeStatus: string
{
    case Active = 'active';
    case InUse = 'in_use';
    case Done = 'done';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
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
