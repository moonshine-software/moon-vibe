<?php

namespace App\Enums;

use MoonShine\Support\Enums\Color;

enum BuildStatus: int
{
    case IN_PROGRESS = 1;

    case ERROR = 2;
    
    case FOR_DOWNLOAD = 3;

    case FOR_TEST = 4;

    public function toString(): string
    {
        return match ($this) {
            self::IN_PROGRESS => __('app.build.status.in_progress'),
            self::ERROR => __('app.build.status.error'),
            self::FOR_DOWNLOAD => __('app.build.status.for_download'),
            self::FOR_TEST => __('app.build.status.for_test'),
        };
    }

    public function color(): Color
    {   
        return match ($this) {
            self::IN_PROGRESS => Color::WARNING,
            self::ERROR => Color::RED,
            self::FOR_DOWNLOAD => Color::GREEN,
            self::FOR_TEST => Color::PURPLE,
        };
    }
}
