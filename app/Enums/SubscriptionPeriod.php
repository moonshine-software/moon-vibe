<?php

namespace App\Enums;

enum SubscriptionPeriod: int
{
    case MONTHLY = 1;

    public function toString(): string
    {
        return match ($this) {
            self::MONTHLY => 'Monthly',
        };
    }

    public function getPeriod(): string
    {
        return match ($this) {
            self::MONTHLY => '1 MONTH',
        };
    }
}
