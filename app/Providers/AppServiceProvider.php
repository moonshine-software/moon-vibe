<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Centrifugo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use MoonShine\AssetManager\Css;
use MoonShine\AssetManager\Js;
use MoonShine\Twirl\Contracts\TwirlBroadcastContract;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            // @phpstan-ignore-next-line
            $this->app['request']->server->set('HTTPS', 'on');
            URL::forceHttps();
        }

        Model::shouldBeStrict(! app()->isProduction());

        $this->app->bind(TwirlBroadcastContract::class, Centrifugo::class);

        moonShineAssets()->add([new Js(Vite::asset('resources/ts/app.ts'))]);
        moonShineAssets()->add([new Css(Vite::asset('resources/css/app.css'))]);
    }
}
