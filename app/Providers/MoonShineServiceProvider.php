<?php

declare(strict_types=1);

namespace App\Providers;

use App\MoonShine\Pages\AboutPage;
use App\MoonShine\Pages\SettingsPage;
use App\MoonShine\Resources\Admin\MoonShineUserResource;
use App\MoonShine\Resources\Admin\MoonShineUserRoleResource;
use App\MoonShine\Resources\Admin\SubscriptionPlanResource;
use App\MoonShine\Resources\ProjectResource;
use App\MoonShine\Resources\ProjectSchemaResource;
use Illuminate\Support\ServiceProvider;
use MoonShine\Contracts\Core\DependencyInjection\ConfiguratorContract;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;
use MoonShine\Laravel\DependencyInjection\MoonShine;
use MoonShine\Laravel\DependencyInjection\MoonShineConfigurator;
use App\MoonShine\Resources\LlmResource;

class MoonShineServiceProvider extends ServiceProvider
{
    /**
     * @param  MoonShine  $core
     * @param  MoonShineConfigurator  $config
     *
     */
    public function boot(CoreContract $core, ConfiguratorContract $config): void
    {
        // $config->authEnable();

        $core
            ->resources([
                MoonShineUserResource::class,
                MoonShineUserRoleResource::class,
                ProjectResource::class,
                ProjectSchemaResource::class,
                SubscriptionPlanResource::class,
                LlmResource::class,
            ])
            ->pages([
                ...$config->getPages(),
                SettingsPage::class,
                AboutPage::class,
            ])
        ;
    }
}
