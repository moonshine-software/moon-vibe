<?php

declare(strict_types=1);

namespace App\Enums;

use MoonShine\Support\Enums\Color;

enum SchemaStatus: int
{
    case PENDING = 1;

    case SUCCESS = 2;

    case ERROR = 3;

    public function toString(): string
    {
        return match ($this) {
            self::PENDING => __('app.schema.generation'),
            self::SUCCESS => __('app.schema.success'),
            self::ERROR => __('app.schema.error'),
        };
    }

    public function color(): Color
    {
        return match ($this) {
            self::PENDING => Color::WARNING,
            self::SUCCESS => Color::GREEN,
            self::ERROR => Color::RED,
        };
    }

    public function label(): string
    {
        return match($this) {
            self::PENDING => __('app.schema.generation'),
            self::SUCCESS => __('app.schema.success'),
            self::ERROR => __('app.schema.error'),
        };
    }
}