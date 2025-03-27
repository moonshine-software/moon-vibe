<?php

declare(strict_types=1);

namespace App\MoonShine\Layouts;

use App\Models\Project;
use App\Models\ProjectSchema;
use App\MoonShine\Pages\Dashboard;
use App\MoonShine\Pages\SettingsPage;
use App\MoonShine\Resources\ProjectSchemaResource;
use MoonShine\AssetManager\InlineCss;
use MoonShine\Contracts\AssetManager\AssetElementContract;
use MoonShine\Laravel\Components\Fragment;
use MoonShine\Laravel\Layouts\CompactLayout;
use MoonShine\ColorManager\ColorManager;
use MoonShine\Contracts\ColorManager\ColorManagerContract;
use MoonShine\Laravel\Components\Layout\{Locales, Notifications, Profile, Search};
use MoonShine\UI\Components\{Breadcrumbs,
    Components,
    Layout\Flash,
    Layout\Div,
    Layout\Body,
    Layout\Burger,
    Layout\Content,
    Layout\Footer,
    Layout\Head,
    Layout\Favicon,
    Layout\Assets,
    Layout\Meta,
    Layout\Header,
    Layout\Html,
    Layout\Layout,
    Layout\Logo,
    Layout\Menu,
    Layout\Sidebar,
    Layout\ThemeSwitcher,
    Layout\TopBar,
    Layout\Wrapper,
    When};
use MoonShine\Laravel\Pages\ProfilePage;
use MoonShine\MenuManager\MenuItem;
use App\MoonShine\Resources\ProjectResource;
use MoonShine\Rush\Services\Rush;

final class MoonShineLayout extends CompactLayout
{
    /**
     * @return list<AssetElementContract>
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
            MenuItem::make('Генерация', Dashboard::class),
            MenuItem::make('Проекты', ProjectResource::class)->badge(fn() => Project::query()->count()),
            MenuItem::make('Настройки', SettingsPage::class),
        ];
    }

    private function getRadius(?string $name = null): string|array
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
            return $r;
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
        ])->collapsed();
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