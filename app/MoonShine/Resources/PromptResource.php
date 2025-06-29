<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Prompt;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\Enums\SortDirection;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

/**
 * @extends ModelResource<Prompt, IndexPage, FormPage, DetailPage>
 */
class PromptResource extends ModelResource
{
    protected string $model = Prompt::class;

    protected string $sortColumn = 'order';

    protected SortDirection $sortDirection = SortDirection::ASC;

    public function getTitle(): string
    {
        return 'Prompt';
    }

    public function indexFields(): iterable
    {
        return [
            ID::make('id'),
            Text::make(__('app.prompt_resource.title'), 'title'),
            Number::make(__('app.prompt_resource.order'), 'order'),
        ];
    }

    public function formFields(): iterable
    {
        return [
            Box::make([
                ID::make('id'),
                Text::make(__('app.prompt_resource.title'), 'title'),
                Textarea::make(__('app.prompt_resource.prompt'), 'prompt')->customAttributes([
                    'rows' => 20,
                ]),
                Number::make(__('app.prompt_resource.order'), 'order'),
            ]),
        ];
    }

    public function detailFields(): iterable
    {
        return [
            ID::make('id'),
            Text::make(__('app.prompt_resource.title'), 'title'),
            Textarea::make(__('app.prompt_resource.prompt'), 'prompt'),
            Number::make(__('app.prompt_resource.order'), 'order'),
        ];
    }

    public function rules(mixed $item): array
    {
        return [
            'title' => ['string', 'required'],
            'prompt' => ['string', 'required'],
        ];
    }
}
