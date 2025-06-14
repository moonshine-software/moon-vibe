<?php

declare(strict_types=1);

namespace App\MoonShine\Layouts;

use App\Enums\Role;
use App\Models\Project;
use App\MoonShine\Pages\AboutPage;
use App\MoonShine\Pages\Dashboard;
use App\MoonShine\Pages\SettingsPage;
use App\MoonShine\Resources\Admin\SubscriptionPlanResource;
use App\MoonShine\Resources\ProjectResource;
use MoonShine\AssetManager\InlineCss;
use MoonShine\ColorManager\ColorManager;
use MoonShine\Contracts\AssetManager\AssetElementContract;
use MoonShine\Contracts\ColorManager\ColorManagerContract;
use MoonShine\Laravel\Components\Fragment;
use MoonShine\Laravel\Components\Layout\{Profile};
use MoonShine\Laravel\Layouts\CompactLayout;
use MoonShine\Laravel\Resources\MoonShineUserResource;
use MoonShine\MenuManager\MenuGroup;
use MoonShine\MenuManager\MenuItem;
use MoonShine\Rush\Services\Rush;
use MoonShine\UI\Components\{Components,
    Layout\Body,
    Layout\Content,
    Layout\Div,
    Layout\Flash,
    Layout\Header,
    Layout\Html,
    Layout\Layout,
    Layout\Menu,
    Layout\Sidebar,
    Layout\Wrapper,
    When};
use App\MoonShine\Resources\LlmResource;

class MoonShineLayout extends CompactLayout
{
    /**
     * @return array|AssetElementContract[]
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function assets(): array
    {
        $assets = parent::assets();

        $assets[] = InlineCss::make(
            "
            :root {
              --radius: " . $this->getRadius('default') . "rem;
              --radius-sm: " . $this->getRadius('sm') . "rem;
              --radius-md: " . $this->getRadius('md') . "rem;
              --radius-lg: " . $this->getRadius('lg') . "rem;
              --radius-xl: " . $this->getRadius('xl') . "rem;
              --radius-2xl: " . $this->getRadius('2xl') . "rem;
              --radius-3xl: " . $this->getRadius('3xl') . "rem;
              --radius-full: " . $this->getRadius('full') . "px;
            }",
        );

        return $assets;
    }

    protected function menu(): array
    {
        return [
            MenuItem::make(__('app.menu.generation'), Dashboard::class)
                ->icon('rocket-launch'),
            MenuItem::make(__('app.menu.projects'), ProjectResource::class)
                ->badge(fn() => Project::query()->where('moonshine_user_id', (int) auth('moonshine')->user()?->id)->count())
                ->icon('square-3-stack-3d'),
            MenuItem::make(__('app.menu.llm'), LlmResource::class)
                ->icon('light-bulb'),
            MenuItem::make(__('app.menu.settings'), SettingsPage::class)
                ->icon('cog-8-tooth'),
            MenuItem::make(__('app.menu.about'), AboutPage::class)
                ->icon('information-circle'),

            MenuGroup::make(static fn () => __('moonshine::ui.resource.system'), [
                MenuItem::make(
                    static fn () => __('moonshine::ui.resource.admins_title'),
                    MoonShineUserResource::class
                ),
                MenuItem::make('Подписки', SubscriptionPlanResource::class),
            ])->canSee(static fn(): bool => auth()->user()?->moonshine_user_role_id === Role::ADMIN),
        ];
    }

    /**
     * @param string|null $name
     *
     * @return string
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function getRadius(?string $name = null): string
    {
        $r = [
            'default' => session()->get('radius.default', '0.15'),
            'sm' => '0.075',
            'md' => '0.275',
            'lg' => '0.3',
            'xl' => '0.4',
            '2xl' => '0.5',
            '3xl' => '1',
            'full' => '9999',
        ];

        if($name === null) {
            return $r['default'];
        }

        return $r[$name];
    }

    /**
     * @param ColorManager $colorManager
     */
    protected function colors(ColorManagerContract $colorManager): void
    {
        parent::colors($colorManager);

        $colorManager
            ->primary('#454b51')
        ;
    }

    public function build(): Layout
    {
        return Layout::make([
            Html::make([
                $this->getHeadComponent(),
                Body::make([
                    Wrapper::make([
                        // $this->getTopBarComponent(),
                        $this->getSidebarComponent(),
                        Div::make([
                            Fragment::make([
                                Flash::make(),

                                //$this->getHeaderComponent(),

                                Content::make([
                                    Components::make(
                                        $this->getPage()->getComponents()
                                    ),
                                ]),
                            ])->class('layout-page')->name(self::CONTENT_FRAGMENT_NAME),
                        ])->class('flex grow overflow-auto')->customAttributes(['id' => self::CONTENT_ID]),
                    ]),
                    Rush::components()->htmlReload(),
                    Rush::components()->jsEvent(),
                ])->class('theme-minimalistic'),
            ])
                ->customAttributes([
                    'lang' => $this->getHeadLang(),
                ])
                ->withAlpineJs()
                ->withThemes(),
        ]);
    }

    protected function getSidebarComponent(): Sidebar
    {
        return Sidebar::make([
            Div::make([
                Menu::make(),
                When::make(
                    fn (): bool => $this->isProfileEnabled(),
                    fn (): array => [
                        $this->getProfileComponent(sidebar: true),
                    ],
                ),
            ])->customAttributes([
                'class' => 'menu',
                ':class' => "asideMenuOpen && '_is-opened'",
                'style' => 'margin-top: 10rem'
            ]),
        ])
            ->collapsed();
    }

    protected function getProfileComponent(bool $sidebar = false): Profile
    {
        return Profile::make(logOutRoute: route('logout'), withBorder: $sidebar);
    }

    protected function getHeaderComponent(): Header
    {
        return Header::make();
    }

    protected function getFooterCopyright(): string
    {
        return '';
    }
}