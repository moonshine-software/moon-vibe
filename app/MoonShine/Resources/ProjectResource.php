<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Project;

use Illuminate\Database\Eloquent\Model;
use MoonShine\Laravel\Fields\Relationships\HasMany;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;

/**
 * @extends ModelResource<Project>
 */
class ProjectResource extends ModelResource
{
    protected string $model = Project::class;

	protected array $with = ['moonshineUser'];

    protected string $column = 'name';

    public function indexFields(): iterable
    {
        return [
			ID::make('id'),
			Text::make('Название', 'name'),
			Text::make('Описание', 'description'),
        ];
    }

    public function formFields(): iterable
    {
        return [
            Box::make([
                ...$this->indexFields(),
                HasMany::make('Схемы', 'schemas', resource: ProjectSchemaResource::class)
                    ->indexButtons([
                        ActionButton::make('Build',
                            url: fn($model) => route('build', ['schemaId' => $model->getKey()])
                        )
                            ->withConfirm(
                                'Выполнить построение проекта?',
                            )
                    ])
                    ->creatable(),
            ])
        ];
    }

    public function detailFields(): iterable
    {
        return [
            ...$this->indexFields()
        ];
    }

    /**
     * @param Project $item
     * @return Project
     */
    public function beforeCreating(mixed $item): mixed
    {
        $item->moonshine_user_id = auth()->user()->id;
        return $item;
    }

    public function filters(): iterable
    {
        return [
        ];
    }

    public function rules(mixed $item): array
    {
        return [
			'name' => ['string', 'required'],
			'description' => ['string', 'nullable']
        ];
    }
}
