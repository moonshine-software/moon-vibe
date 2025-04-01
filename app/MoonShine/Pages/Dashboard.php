<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;


use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use MoonShine\UI\Components\Layout\Divider;
use MoonShine\UI\Fields\Text;
use MoonShine\Laravel\Pages\Page;
use MoonShine\UI\Components\Tabs;
use MoonShine\UI\Fields\Textarea;
use MoonShine\UI\Components\Tabs\Tab;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\UI\Components\FlexibleRender;
use MoonShine\Contracts\UI\ComponentContract;

#[\MoonShine\MenuManager\Attributes\SkipMenu]
class Dashboard extends Page
{
    /**
     * @return list<ComponentContract>
     */
    protected function components(): iterable
	{
        $templates = Str::markdown(Storage::disk('local')->get('generate/template-ru.md'));
        $example1 = Str::markdown(Storage::disk('local')->get('generate/example1-ru.md'));
        $example2 = Str::markdown(Storage::disk('local')->get('generate/example2-ru.md'));


		return [
            FormBuilder::make(route('ai-request.request'), fields: [
                Text::make(__('moonshine.dashboard.project_name'), 'project_name'),
                Textarea::make(__('moonshine.dashboard.prompt'), 'prompt')->customAttributes([
                    'placeholder' => __('moonshine.dashboard.prompt_placeholder'),
                    'rows' => 12,
                ])
            ])
                ->submit(__('moonshine.dashboard.submit')),

            Divider::make(),

            Tabs::make([
                Tab::make(__('moonshine.dashboard.templates'), [
                    Box::make([
                        FlexibleRender::make($templates),
                    ])
                ]),
                Tab::make(__('moonshine.dashboard.example-1'), [
                    Box::make([
                        FlexibleRender::make($example1),
                    ])
                ]),
                Tab::make(__('moonshine.dashboard.example-2'), [
                    Box::make([
                        FlexibleRender::make($example2),
                    ])
                ]),
                Tab::make(__('moonshine.dashboard.example-3'), [
                    Box::make([
                        FlexibleRender::make('HTML'),
                    ])
                ])
            ])
        ];
	}
}
