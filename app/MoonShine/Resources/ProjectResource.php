<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Project;

use Closure;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Laravel\Enums\Action;
use MoonShine\Laravel\Fields\Relationships\HasMany;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Fields\StackFields;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

/**
 * @extends ModelResource<Project>
 */
class ProjectResource extends ModelResource
{
    protected string $model = Project::class;

	protected array $with = ['moonshineUser'];

    protected string $column = 'name';

    protected function activeActions(): ListOf
    {
        return new ListOf(Action::class, [
            Action::CREATE,
            Action::UPDATE,
            Action::DELETE,
        ]);
    }

    public function indexFields(): iterable
    {
        return [
            StackFields::make('')->fields(function(StackFields $fields){
                return [
                    Text::make('', 'name')->changePreview(fn(string $value) => "<b>$value</b><hr>"),
                    Textarea::make('', 'description'),
                ];
            }),
        ];
    }

    public function formFields(): iterable
    {
        return [
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
                ->searchable(false)
                ->creatable(),

        ];
    }

    protected function trAttributes(): Closure
    {
        return fn(?DataWrapperContract $data, int $row) => [
            'style' => 'margin-bottom: 5rem'
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
