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
            self::IN_PROGRESS => __('moonshine.build.status.in_progress'),
            self::ERROR => __('moonshine.build.status.error'),
            self::COMPLETED => __('moonshine.build.status.completed'),
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
