<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Centrifugo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use MoonShine\AssetManager\Js;
use MoonShine\Rush\Contracts\RushBroadcastContract;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::shouldBeStrict(! app()->isProduction());

        $this->app->bind(RushBroadcastContract::class, Centrifugo::class);

        moonShineAssets()->add([new Js(Vite::asset('resources/ts/app.ts'))]);
    }
}
