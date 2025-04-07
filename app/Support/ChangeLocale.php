<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Cookie;

class ChangeLocale
{
    public static function set(string $locale, bool $isSetCookie = true): void
    {
        Cookie::queue('lang', $locale);

        app()->setLocale($locale);

        moonshineConfig()->locale($locale);
    }
}