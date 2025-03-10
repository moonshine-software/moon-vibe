<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# Laravel 12 blank project

---
| Included         |
|------------------|
| ✅ Basic setting  |
| ✅ PhpStan        |
| ✅ Php CS Fixer   |
| ✅ TypeScript     |
| ✅ Xdebug         |
| ✅ Docker         |
| ✅ GitHub actions |

## Installation
- Run the git clone command `git clone git@github.com:dev-lnk/laravel-blank.git .`.
- Copy the `.env.example` file and rename it to `.env`, customize the `#Docker` section to your needs.
- Run the command `make build`, and then `make install`.
- Check the application's operation using the link `http://localhost` or `http://localhost:${APP_WEB_PORT}`.
- Run stat analysis and tests using the command `make test`.

## About
This is a blank Laravel 12 project set up to get started with development. What the setup includes:
- Configured docker for local development.
- Middleware is configured in a separate file.
```php
namespace App\Http\Middleware;

use Illuminate\Foundation\Configuration\Middleware;

class MiddlewareHandler
{
    protected array $aliases = [
        //'auth' => AuthMiddleware::class
    ];

    public function __invoke(Middleware $middleware): Middleware
    {
        if ($this->aliases) {
            $middleware->alias($this->aliases);
        }
        return $middleware;
    }
}
```
- Cron jobs are configured in a separate file.
```php
namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;

class ScheduleHandler
{
    public function __invoke(Schedule $schedule): void
    {
        //$schedule->command(HealthCommand::class)->hourly();
    }
}
```
- Exception handling is configured in a separate file
```php
namespace App\Exceptions;

use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Http\Request;

class ExceptionsHandler
{
    public function __invoke(Exceptions $exceptions): Exceptions
    {
        $exceptions->renderable(
            function (NotFoundHttpException $e, ?Request $request = null) {
                if($request?->is('api/*')) {
                    return $this->jsonResponse($e->getStatusCode());
                }
                return response()->view('errors.404', status: $e->getStatusCode());
            }
        );

        return $exceptions;
    }

    private function jsonResponse(int $code): JsonResponse
    {
        return response()->json([
            'error' => "HTTP error: $code"
        ])->setStatusCode($code);
    }
}
```
- Configured tests.
```php
namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('optimize:clear');

        Notification::fake();

        Http::preventStrayRequests();

        $this->withoutVite();
    }
}
```
- Added RouteServiceProvider
```php
namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
//        $this->routes(function () {
//            Route::middleware(['web', 'app.auth'])
//                ->namespace($this->namespace)
//                ->prefix('my')
//                ->group(base_path('routes/my.php'));
//        });
    }
}
```
- Installed and configured phpstan (max level).
- Installed and configured TypeScript, used instead of JavaScript.

The final `bootstrap/app.php` file looks like this:

```php
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
```

## Docker

### Images

- nginx:1.27.3-alpine
- php:8.4.4-fpm (with xdebug)
- mysql:9.2.0
- redis:7.0.11-alpine
- node:23.6.1-alpine3.18

### Other
- Many commands to speed up development and work with docker can be found in the `Makefile`
- If you don't need Docker, remove: `/docker`, `docker-compose.yml`, `Makefile`. Convert `.env` to standard Laravel form
- To launch containers with `worker` and `scheduler`, delete comments on the corresponding blocks in `docker-compose.yml`