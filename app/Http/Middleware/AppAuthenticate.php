<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use MoonShine\Laravel\Http\Middleware\Authenticate;

class AppAuthenticate extends Authenticate
{
    protected function redirectTo($request): string
    {
        return route('login');
    }
}
