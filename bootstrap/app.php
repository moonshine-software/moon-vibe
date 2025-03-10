<?php

use App\Console\ScheduleHandler;
use App\Exceptions\ExceptionsHandler;
use App\Http\Middleware\MiddlewareHandler;
use Illuminate\Foundation\Application;

return Application::configure(basePath: dirname(__DIR__))
    ->withMiddleware(new MiddlewareHandler())
    ->withSchedule(new ScheduleHandler())
    ->withExceptions(new ExceptionsHandler())
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->create();
