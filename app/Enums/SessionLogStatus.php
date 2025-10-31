<?php

namespace App\Enums;

enum SessionLogStatus: string
{
    case Ongoing = 'ongoing';
    case Finished = 'finished';

    public function label(): string
    {
        return match ($this) {
            self::Ongoing => 'Ongoing',
            self::Finished => 'Finished',
        };
    }
}
