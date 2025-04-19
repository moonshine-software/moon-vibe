<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use Closure;

use App\Models\Project;
use App\Models\MoonShineUser;
use App\Models\ProjectSchema;
use MoonShine\Support\ListOf;
use MoonShine\UI\Fields\Text;
use App\Services\SimpleSchema;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Textarea;
use MoonShine\Support\Enums\Color;
use MoonShine\UI\Components\Badge;
use MoonShine\Laravel\Enums\Action;
use MoonShine\UI\Fields\StackFields;
use MoonShine\Support\Enums\HttpMethod;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\UI\Components\ActionButton;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\UI\Components\FlexibleRender;
use MoonShine\UI\Components\Layout\Divider;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Laravel\Resources\ModelResource;
use App\MoonShine\Pages\Project\ProjectFormPage;
use MoonShine\Contracts\UI\ActionButtonContract;
use Illuminate\Contracts\Database\Eloquent\Builder;
use MoonShine\Laravel\Fields\Relationships\HasMany;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use DevLnk\MoonShineBuilder\Services\CodeStructure\Factories\StructureFromArray;

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
    
    protected function pages(): array
    {
        return [
            IndexPage::class,
            ProjectFormPage::class,
            DetailPage::class,
        ];
    }

    public function indexFields(): iterable
    {
        return [
            StackFields::make('')->fields(function(StackFields $fields){
                return [
                    Text::make('', 'name')->changePreview(fn(string $value) => "<b>$value</b><hr>"),
                    Textarea::make('', 'description')->customAttributes([
                        'rows' => 7,
                    ]),
                ];
            }),
        ];
    }

    protected function modifyQueryBuilder(Builder $builder): Builder
    {
        return $builder->where('moonshine_user_id', auth('moonshine')->user()->id);
    }

    public function formFields(): iterable
    {
        /** @var MoonShineUser $user */
        $user = auth('moonshine')->user();
        $generationsLeft = $user->getGenerationsLeft();

        return [
            ...$this->indexFields(),
            HasMany::make(__('app.project.schemas'), 'schemas', resource: ProjectSchemaResource::class)
                ->indexButtons([
                    ActionButton::make(__('app.project.create'),
                        url: fn($model) => route('build', ['schemaId' => $model->getKey()])
                    )
                        ->async(HttpMethod::POST)
                        ->withConfirm(
                            '',
                            __('app.project.build_confirm'),
                        )
                    ,
                    ActionButton::make(__('app.project.correct'),
                        url: fn($model) => route('ai-request.correct', ['schemaId' => $model->getKey()])
                    )
                        ->withConfirm(
                            __('app.project.correction'),
                            formBuilder: fn(FormBuilder $builder, ProjectSchema $schema) => $builder
                                ->fields([
                                    Preview::make('', formatted: function () use ($schema) {
                                        if($schema->schema === null) {
                                            return '';
                                        }

                                        try {
                                            $simpleSchema = new SimpleSchema((new StructureFromArray(json_decode($schema->schema, true)))->makeStructures());
                                            return $simpleSchema->generate();
                                        } catch (\Throwable) {
                                            return '';
                                        }
                                    }),
                                    FlexibleRender::make(
                                        (string) Badge::make(
                                            __('app.generations_left', ['generations' => $generationsLeft]),
                                            $generationsLeft > 0 ? Color::GREEN : Color::RED
                                        )
                                    ),
                                    Divider::make(),
                                    Textarea::make(__('app.project.prompt'), 'prompt')->customAttributes([
                                        'rows' => 6,
                                    ])
                                ])
                        )
                ])
                ->searchable(false)
                ->creatable(),
        ];
    }

    protected function formButtons(): ListOf
    {
        return new ListOf(ActionButtonContract::class, []);
    }

    public function modifyListComponent(ComponentContract $component): ComponentContract
    {
        return parent::modifyListComponent($component)->customAttributes([
            'style' => 'border-spacing: 0rem 1.2rem;'
        ]);
    }

    protected function trAttributes(): Closure
    {
        return fn(?DataWrapperContract $data, int $row) => [
            //'style' => 'margin-bottom: 5rem'
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
