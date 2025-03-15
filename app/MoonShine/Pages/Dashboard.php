<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;


use MoonShine\Laravel\Pages\Page;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

#[\MoonShine\MenuManager\Attributes\SkipMenu]

class Dashboard extends Page
{
    /**
     * @return array<string, string>
     */
    public function getBreadcrumbs(): array
    {
        return [
            '#' => $this->getTitle()
        ];
    }

    public function getTitle(): string
    {
        return $this->title ?: 'Dashboard';
    }

    /**
     * @return list<ComponentContract>
     */
    protected function components(): iterable
	{
		return [
            FormBuilder::make(route('ai-request'), fields: [
                Text::make('Название проекта', 'project_name'),
                Textarea::make('Запрос', 'promt')->customAttributes([
                    'rows' => 6,
                ])
            ])
                ->submit('Отправить')
        ];
	}
}
