<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Enums\SchemaStatus;
use App\Models\ProjectSchema;

use App\Support\SchemaValidator;
use Illuminate\Database\Eloquent\Model;
use MoonShine\Laravel\Enums\Action;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\Enums\Color;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Components\Badge;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\Enum;
use MoonShine\UI\Fields\ID;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

/**
 * @extends ModelResource<ProjectSchema>
 */
class ProjectSchemaResource extends ModelResource
{
    protected string $model = ProjectSchema::class;

	protected array $with = ['project'];

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
			BelongsTo::make('Проект', 'project', resource: ProjectResource::class),
            Preview::make('Статус', formatted: function (ProjectSchema $schema) {
                if($schema->status_id === SchemaStatus::ERROR) {
                    return (string) Badge::make('Ошибка: ' . $schema->error, Color::RED)->customAttributes([
                        'class' => 'schema-id-' . $schema->id
                    ]);
                }
                return (string) Badge::make($schema->status_id->toString(), $schema->status_id->color())->customAttributes([
                    'class' => 'schema-id-' . $schema->id
                ]);
            }),
        ];
    }

    public function formFields(): iterable
    {
        return [
            Box::make([
                ID::make('id'),
                BelongsTo::make('Проект', 'project', resource: ProjectResource::class),
                Textarea::make('', 'schema')->changeFill(function(ProjectSchema $data, Textarea $field){
                    $field->customAttributes([
                        'class' => 'schema-edit-id-' . $data->id,
                        'rows' => 20,
                    ]);
                    return $data->schema;
                }),
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
        $item->error = '';
        try {
            (new SchemaValidator(request()->input('schema')))
                ->validate();
        }catch (\Throwable $e) {
            $item->error = $e->getMessage();
        }

        return $item;
    }

    /**
     * @throws \Exception
     */
    protected function beforeUpdating(mixed $item): mixed
    {
        $item->error = '';
        try {
            (new SchemaValidator(request()->input('schema')))
                ->validate();
        }catch (\Throwable $e) {
            $item->error = $e->getMessage();
        }

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
			'project_id' => ['int', 'required'],
			'schema' => ['string', 'required'],
        ];
    }
}
