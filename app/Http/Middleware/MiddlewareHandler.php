<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Foundation\Configuration\Middleware;

class MiddlewareHandler
{
    /** @var array<string, class-string> */
    protected array $aliases = [

    ];

    public function __invoke(Middleware $middleware): Middleware
    {
        if ($this->aliases) {
            $middleware->alias($this->aliases);
        }

        return $middleware;
    }
}
