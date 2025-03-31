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
     * @return list<ComponentContract>
     */
    protected function components(): iterable
	{
		return [
            FormBuilder::make(route('ai-request.request'), fields: [
                Text::make(__('moonshine.dashboard.project_name'), 'project_name'),
                Textarea::make(__('moonshine.dashboard.prompt'), 'prompt')->customAttributes([
                    'placeholder' => __('moonshine.dashboard.prompt_placeholder'),
                    'rows' => 6,
                ])
            ])
                ->submit(__('moonshine.dashboard.submit'))
        ];
	}
}
