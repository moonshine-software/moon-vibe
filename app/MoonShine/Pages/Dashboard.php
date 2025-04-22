<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use App\Models\MoonShineUser;
use MoonShine\UI\Fields\Text;
use MoonShine\Laravel\Pages\Page;
use MoonShine\UI\Components\Tabs;
use MoonShine\UI\Fields\Textarea;
use MoonShine\Support\Enums\Color;
use MoonShine\UI\Components\Badge;
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

        /** @var MoonShineUser $user */
        $user = auth('moonshine')->user();
        $generationsLeft = $user->getGenerationsLeft();
        
		return [
            FormBuilder::make(route('ai-request.request'), fields: [
                FlexibleRender::make(
                    (string) Badge::make(
                        __('app.generations_left', ['generations' => $generationsLeft]),
                        $generationsLeft > 0 ? Color::GREEN : Color::RED
                    )
                ),
                Text::make(__('app.dashboard.project_name'), 'project_name'),
                Textarea::make(__('app.dashboard.prompt'), 'prompt')->customAttributes([
                    'placeholder' => __('app.dashboard.prompt_placeholder'),
                    'rows' => 12,
                ]),
            ])
                ->submit(__('app.dashboard.submit'), [
                    'class' => 'btn-primary btn-lg',
                ])
            ,

            Divider::make(),

            Tabs::make([
                Tab::make(__('app.dashboard.types'), [
                    Box::make([
                        FlexibleRender::make($types),
                    ])
                ]),
                Tab::make(__('app.dashboard.example-1'), [
                    Box::make([
                        FlexibleRender::make($example1),
                    ])
                ]),
                Tab::make(__('app.dashboard.example-2'), [
                    Box::make([
                        FlexibleRender::make($example2),
                    ])
                ]),
                Tab::make(__('app.dashboard.example-3'), [
                    Box::make([
                        FlexibleRender::make($example3),
                    ])
                ])
            ])
        ];
	}
}
