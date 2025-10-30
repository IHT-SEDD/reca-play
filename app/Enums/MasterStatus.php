<?php

namespace App\Enums;

enum MasterStatus: int
{
    case Active = 1;
    case Inactive = 0;

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Inactive => 'In Active',
        };
    }

    public function isActive(): bool
    {
        return $this === self::Active;
    }
    public function isInactive(): bool
    {
        return $this === self::Inactive;
    }
}
