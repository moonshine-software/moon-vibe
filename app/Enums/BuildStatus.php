<?php

namespace App\Enums;

use MoonShine\Support\Enums\Color;

enum BuildStatus: int
{
    case IN_PROGRESS = 1;

    case ERROR = 2;
    
    case COMPLETED = 3;

    public function toString(): string
    {
        return match ($this) {
            self::IN_PROGRESS => __('app.build.status.in_progress'),
            self::ERROR => __('app.build.status.error'),
            self::COMPLETED => __('app.build.status.completed'),
        };
    }

    public function color(): Color
    {   
        return match ($this) {
            self::IN_PROGRESS => Color::WARNING,
            self::ERROR => Color::RED,
            self::COMPLETED => Color::GREEN,
        };
    }
}
