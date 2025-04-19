<?php

namespace App\Enums;

enum ResetPeriod: int
{
    case MONTHLY = 1;

    public function toString(): string
    {
        return match ($this) {
            self::MONTHLY => 'Monthly',
        };
    }
}