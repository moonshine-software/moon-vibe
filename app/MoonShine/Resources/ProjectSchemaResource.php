<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\ProjectSchema;

use App\Support\SchemaValidator;
use Illuminate\Database\Eloquent\Model;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\UI\Fields\Textarea;

/**
 * @extends ModelResource<ProjectSchema>
 */
class ProjectSchemaResource extends ModelResource
{
    protected string $model = ProjectSchema::class;

	protected array $with = ['project'];

    public function indexFields(): iterable
    {
        return [
			ID::make('id'),
			BelongsTo::make('Проект', 'project', resource: ProjectResource::class),
        ];
    }

    public function formFields(): iterable
    {
        return [
            Box::make([
                ...$this->indexFields(),
                Textarea::make('Json схема', 'schema')->customAttributes([
                    'rows' => 20,
                ])
                ,
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
     * @throws \Exception
     */
    protected function beforeCreating(mixed $item): mixed
    {
        (new SchemaValidator(request()
            ->input('schema')))
            ->validate();
        return $item;
    }

    /**
     * @throws \Exception
     */
    protected function beforeUpdating(mixed $item): mixed
    {
        (new SchemaValidator(request()
            ->input('schema')))
            ->validate();
        return $item;
    }

    protected function indexButtons(): ListOf
    {
        return parent::indexButtons()
            ->prepend(
                ActionButton::make(
                    'Build',
                    fn(Model $item) => route('build', ['schema_id' => $item->getKey()])
                )
            );
    }

    public function filters(): iterable
    {
        return [
        ];
    }

    public function rules(mixed $item): array
    {
        return [
			'project_id' => ['int', 'required'],
			'schema' => ['string', 'required'],
        ];
    }
}
