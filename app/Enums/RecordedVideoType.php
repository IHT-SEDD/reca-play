<?php

namespace App\Enums;

enum RecordedVideoType: string
{
    case Record = 'record';
    case Highlight = 'highlight';

    public function label(): string
    {
        return match ($this) {
            self::Record => 'Record',
            self::Highlight => 'Highlight',
        };
    }

    public function isRecord(): bool
    {
        return $this === self::Record;
    }
    public function isHighlight(): bool
    {
        return $this === self::Highlight;
    }
}
