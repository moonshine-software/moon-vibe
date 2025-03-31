<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use MoonShine\Laravel\Pages\Page;
use LogicException;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\FormBuilderContract;
use MoonShine\Laravel\Http\Controllers\ProfileController;
use MoonShine\Laravel\MoonShineAuth;
use MoonShine\Laravel\Traits\WithComponentsPusher;
use MoonShine\Laravel\TypeCasts\ModelCaster;
use MoonShine\MenuManager\Attributes\SkipMenu;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Div;
use MoonShine\UI\Components\Layout\Head;
use MoonShine\UI\Components\Tabs;
use MoonShine\UI\Components\Tabs\Tab;
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
        return __('moonshine.menu.about');
    }

    /**
     * @return list<ComponentContract>
     */
    protected function components(): iterable
    {
        return [
            Box::make([
                Div::make([
                    Preview::make()->setValue("<h1>Цель.</h1><br>Данное приложение позволяет по описанию проекта сгенерировать схему админ-панели MoonShine и быстро развернуть базовый, рабочий вариант на своем окружении.<br><br><h1>Как работает прилложение.</h1><br>Данный проект позволяет с помощью ИИ создать схему для пакета <a href='https://github.com/dev-lnk/moonshine-builder' class='link'>MoonShineBuilder</a>, который сгенерирует ресурсы, модели и миграции в админ-панели MoonShine.<br><br>После генерации схемы вы можете выполнить построение проекта, который будет упакован в tar архив. В данном архиве будет содержаться проект Laravel, с предустановленным MoonShine и построенными сущностями. За основу проекта берется <a href='https://github.com/dev-lnk/moonshine-blank' class='link'>репозиторий</a> из <a href='" . toPage(SettingsPage::class). "' class='link'>настроек</a>, но вы можете указать свой.")
                ])
            ])->customAttributes([
                'style' => 'width: 50%; margin: 0 auto;'
            ]),
        ];
    }
}
