<?php

namespace App\Enums;

enum SelfieSessionStatus: string
{
    case Prepare = 'prepare';
    case Ongoing = 'ongoing';

    public function label(): string
    {
        return match ($this) {
            self::Prepare => 'Prepare',
            self::Ongoing => 'Ongoing',
        };
    }

    public function isOngoing(): bool
    {
        return $this === self::Ongoing;
    }
}
