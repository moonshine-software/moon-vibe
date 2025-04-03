<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use MoonShine\UI\Fields\Text;
use MoonShine\Laravel\Pages\Page;
use MoonShine\UI\Components\Tabs;
use MoonShine\UI\Fields\Textarea;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use MoonShine\UI\Components\Tabs\Tab;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\UI\Components\FlexibleRender;
use MoonShine\UI\Components\Layout\Divider;
use MoonShine\Contracts\UI\ComponentContract;

class Dashboard extends Page
{
    /**
     * @return list<ComponentContract>
     */
    protected function components(): iterable
	{

        $lang = App::getLocale();

        $types = View::make("generate-page.examples.{$lang}.types");
        $example1 = View::make("generate-page.examples.{$lang}.example-1");
        $example2 = View::make("generate-page.examples.{$lang}.example-2");
        $example3 = View::make("generate-page.examples.{$lang}.example-3");

		return [
            FormBuilder::make(route('ai-request.request'), fields: [
                Text::make(__('moonshine.dashboard.project_name'), 'project_name'),
                Textarea::make(__('moonshine.dashboard.prompt'), 'prompt')->customAttributes([
                    'placeholder' => __('moonshine.dashboard.prompt_placeholder'),
                    'rows' => 12,
                ])
            ])
                ->submit(__('moonshine.dashboard.submit'))
                ->submitN
                ,

            Divider::make(),

            Tabs::make([
                Tab::make(__('moonshine.dashboard.types'), [
                    Box::make([
                        FlexibleRender::make($types),
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
                        FlexibleRender::make($example3),
                    ])
                ])
            ])
        ];
	}
}
