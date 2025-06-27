<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Enums\SchemaStatus;
use App\Models\ProjectSchema;
use App\Services\SchemaValidator;
use App\Services\SimpleSchema;
use DevLnk\MoonShineBuilder\Services\CodeStructure\Factories\StructureFromArray;
use MoonShine\Laravel\Enums\Action;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\Enums\Color;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\Badge;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Textarea;

/**
 * @extends ModelResource<ProjectSchema, IndexPage, FormPage, DetailPage>
 */
class ProjectSchemaResource extends ModelResource
{
    protected string $model = ProjectSchema::class;

    /** @var string[] */
	protected array $with = ['project'];

    protected bool $detailInModal = true;

    protected function activeActions(): ListOf
    {
        return new ListOf(Action::class, [
            Action::CREATE,
            Action::VIEW,
            Action::UPDATE,
            Action::DELETE,
        ]);
    }

    /**
     * @return string[]
     */
    protected function pages(): array
    {
        return [
            IndexPage::class,
            FormPage::class,
            DetailPage::class,
            //SchemaDetailPage::class,
        ];
    }

    public function indexFields(): iterable
    {
        return [
            Preview::make(__('app.schema.status'), formatted: function (ProjectSchema $schema) {
                if($schema->status_id === SchemaStatus::ERROR) {
                    return (string) Badge::make('Ошибка: ' . $schema->error, Color::RED)->customAttributes([
                        'class' => 'schema-id-' . $schema->id
                    ]);
                }
                return (string) Badge::make($schema->status_id->toString(), $schema->status_id->color())->customAttributes([
                    'class' => 'schema-id-' . $schema->id
                ]);
            }),

            Preview::make(__('app.schema.preview'), formatted: function (ProjectSchema $schema) {
                if($schema->schema === null) {
                    return '';
                }
                try {
                    $simpleSchema = new SimpleSchema((new StructureFromArray(json_decode($schema->schema, true)))->makeStructures());
                    return $simpleSchema->generate(false);
                } catch (\Throwable) {
                    return '';
                }
            })
        ];
    }

    public function formFields(): iterable
    {
        return [
            Box::make([
                ID::make('id'),
                BelongsTo::make(__('app.schema.project'), 'project', resource: ProjectResource::class),
                Textarea::make('', 'schema')->changeFill(function(ProjectSchema $data, Textarea $field): string {
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
            Preview::make(__('app.schema.preview'), formatted: function (ProjectSchema $schema) {
                if($schema->schema === null) {
                    return '';
                }
                $simpleSchema = new SimpleSchema(
                    (new StructureFromArray(json_decode($schema->schema, true))
                )->makeStructures());
                return $simpleSchema->generate();
            })
        ];
    }

    /**
     * @throws \Exception
     */
    protected function beforeCreating(mixed $item): mixed
    {
        $item->error = (new SchemaValidator())
            ->validate(request()->string('schema')->value());

        $item->status_id = $item->error === '' ? SchemaStatus::SUCCESS : SchemaStatus::ERROR;

        return $item;
    }

    /**
     * @throws \Exception
     */
    protected function beforeUpdating(mixed $item): mixed
    {
        $item->error = (new SchemaValidator())
            ->validate(request()->string('schema')->value());

        $item->status_id = $item->error === '' ? SchemaStatus::SUCCESS : SchemaStatus::ERROR;

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
