<?php

namespace App\Enums;

enum RobotsDirective: string
{
    case IndexFollow = 'index,follow';
    case NoIndexFollow = 'noindex,follow';
    case IndexNoFollow = 'index,nofollow';
    case NoIndexNoFollow = 'noindex,nofollow';

    public function label(): string
    {
        return match ($this) {
            self::IndexFollow => 'Index, follow (recommended)',
            self::NoIndexFollow => 'No index, follow',
            self::IndexNoFollow => 'Index, no follow',
            self::NoIndexNoFollow => 'No index, no follow',
        };
    }
}
