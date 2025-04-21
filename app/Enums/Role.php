<?php

declare(strict_types=1);

namespace App\Enums;

enum Role: int
{
    case ADMIN = 1;

    case USER = 2;

    public function toString(): string
    {
        return match($this) {
            self::ADMIN => 'Admin',
            self::USER => 'User',
        };
    }
}
