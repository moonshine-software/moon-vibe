<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use App\Models\MoonShineUser;
use App\Repositories\LlmRepository;
use MoonShine\UI\Fields\Select;
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

        $types = View::make("generate-page.examples.types");
        $fields = View::make("generate-page.examples.fields");

        /** @var MoonShineUser $user */
        $user = auth('moonshine')->user();
        $generationLeftInfo = $user->getGenerationLeftInfo();

        $llmRepository = new LlmRepository();
        $llms = $llmRepository->getLlms();
        $defaultId = $llmRepository->getDefaultLlmId();

		return [
            FormBuilder::make(route('ai-request.request'), fields: [
                FlexibleRender::make(
                    (string) Badge::make(
                        __('app.generations_left', ['generations' => $generationLeftInfo['info']]),
                        $generationLeftInfo['color']
                    )
                ),
                Text::make(__('app.dashboard.project_name'), 'project_name'),
                Select::make('LLM', 'llm_id')
                    ->options($llms)
                    ->when(
                        $defaultId !== null,
                        fn (Select $select) => $select->default($defaultId)
                    ),
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
                Tab::make(__('app.dashboard.fields'), [
                    Box::make([
                        FlexibleRender::make($fields),
                    ])
                ])
            ])
        ];
	}
}
