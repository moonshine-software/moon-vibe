<?php

declare(strict_types=1);

namespace App\Providers;

use App\Api\DeepSeek;
use App\Services\Centrifugo;
use App\Services\CutCodeAgent;
use Illuminate\Support\Facades\URL;
use MoonShine\AssetManager\Js;
use MoonShine\AssetManager\Css;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use App\Contracts\SchemaGenerateContract;
use MoonShine\Rush\Contracts\RushBroadcastContract;

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
            $this->app['request']->server->set('HTTPS','on');
            URL::forceHttps();
        }

        Model::shouldBeStrict(! app()->isProduction());

        $this->app->bind(RushBroadcastContract::class, Centrifugo::class);

        moonShineAssets()->add([new Js(Vite::asset('resources/ts/app.ts'))]);
        moonShineAssets()->add([new Css(Vite::asset('resources/css/app.css'))]);

        //$this->app->bind(SchemaGenerateContract::class, DeepSeek::class);
        $this->app->bind(SchemaGenerateContract::class, CutCodeAgent::class);
    }
}
