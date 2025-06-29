<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Laravel\Pages\Page;
use MoonShine\MenuManager\Attributes\SkipMenu;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Div;
use MoonShine\UI\Fields\Preview;

#[SkipMenu]
/**
 * @extends Page<null>
 */
class AboutPage extends Page
{
    /**
     * @return array<string, string>
     */
    public function getBreadcrumbs(): array
    {
        return [
            '#' => $this->getTitle(),
        ];
    }

    public function getTitle(): string
    {
        return __('app.menu.about');
    }

    /**
     * @return list<ComponentContract>
     */
    protected function components(): iterable
    {
        return [
            Box::make([
                Div::make([
                    Preview::make()->setValue(__('app.about.content')),
                ]),
            ])->customAttributes([
                'style' => 'width: 50%; margin: 0 auto;',
            ]),
        ];
    }
}
