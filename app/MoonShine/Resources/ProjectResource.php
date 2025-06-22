<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\LargeLanguageModel;
use Closure;

use App\Models\Project;
use App\Models\MoonShineUser;
use App\Models\ProjectSchema;
use MoonShine\Contracts\UI\FormBuilderContract;
use MoonShine\Contracts\UI\HasFieldsContract;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\Modal;
use MoonShine\UI\Components\OffCanvas;
use MoonShine\UI\Fields\Fieldset;
use MoonShine\UI\Fields\Text;
use App\Services\SimpleSchema;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Textarea;
use MoonShine\UI\Components\Badge;
use MoonShine\Laravel\Enums\Action;
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
use DevLnk\MoonShineBuilder\Services\CodeStructure\Factories\StructureFromArray;
use Throwable;

/**
 * @extends ModelResource<Project, IndexPage, ProjectFormPage, DetailPage>
 */
class ProjectResource extends ModelResource
{
    protected string $model = Project::class;

    /** @var string[]  */
	protected array $with = ['moonshineUser', 'llm'];

    protected string $column = 'name';

    protected function activeActions(): ListOf
    {
        return new ListOf(Action::class, [
            Action::CREATE,
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
            ProjectFormPage::class,
            DetailPage::class,
        ];
    }

    public function indexFields(): iterable
    {
        return [
            Fieldset::make('', [
                Text::make('', 'name')->changePreview(fn(string $value) => "<b>$value</b><hr>"),
                Textarea::make('', 'description')->customAttributes([
                    'rows' => 7,
                ]),
            ]),
        ];
    }

    protected function modifyQueryBuilder(Builder $builder): Builder
    {
        return $builder->where('moonshine_user_id', (int) auth('moonshine')->user()?->id);
    }

    public function formFields(): iterable
    {
        /** @var MoonShineUser $user */
        $user = auth('moonshine')->user();
        $generationLeftInfo = $user->getGenerationLeftInfo();

        return [
            Text::make('', 'name')->changePreview(
                static fn(string $value, Text $ctx): string => "<b>$value</b><hr>"
            ),
            Textarea::make('', 'description')->customAttributes([
                'rows' => 7,
            ]),
            BelongsTo::make('LLM', 'llm',
                formatted: fn(LargeLanguageModel $item) => "{$item->provider->toString()} ($item->model)",
                resource: LlmResource::class
            )->nullable(),
            HasMany::make(__('app.project.schemas'), 'schemas', resource: ProjectSchemaResource::class)
                ->indexButtons([
                    ActionButton::make(__('app.project.create'),
                        url: fn(ProjectSchema $model) => route('build', ['schemaId' => $model->getKey()])
                    )
                        ->async(HttpMethod::POST)
                        ->withConfirm(
                            '',
                            __('app.project.build_confirm'),
                        )
                    ,
                    ActionButton::make(__('app.project.correct'),
                        url: fn(ProjectSchema $model) => route('ai-request.correct', ['schemaId' => $model->getKey()])
                    )
                        ->withConfirm(
                            __('app.project.correction'),
                            formBuilder: function (
                                FormBuilderContract $builder,
                                ProjectSchema $schema
                            ) use ($generationLeftInfo): FormBuilderContract {
                                $builder
                                    ->fields([
                                        Preview::make('', formatted: function () use ($schema) {
                                            if($schema->schema === null) {
                                                return '';
                                            }
                                            try {
                                                $simpleSchema = new SimpleSchema(
                                                    new StructureFromArray(json_decode($schema->schema, true)
                                                )->makeStructures());
                                                return $simpleSchema->generate();
                                            } catch (Throwable) {
                                                return '';
                                            }
                                        }),
                                        FlexibleRender::make(
                                            (string) Badge::make(
                                                __('app.generations_left', ['generations' => $generationLeftInfo['info']]),
                                                $generationLeftInfo['color']
                                            )
                                        ),
                                        Divider::make(),
                                        Textarea::make(__('app.project.prompt'), 'prompt')->customAttributes([
                                            'rows' => 6,
                                        ])
                                    ]);
                                return $builder;
                            },
                        )
                ])
                ->searchable(false)
                ->creatable(),
        ];
    }

    protected function formButtons(): ListOf
    {
        return parent::formButtons()->empty();
    }

    public function modifyListComponent(ComponentContract $component): ComponentContract
    {
        return parent::modifyListComponent($component)->customAttributes([
            'style' => 'border-spacing: 0rem 1.2rem;'
        ]);
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
        $item->moonshine_user_id = (int) auth()->user()?->id;
        return $item;
    }

    public function rules(mixed $item): array
    {
        return [
			'name' => ['string', 'required'],
			'description' => ['string', 'nullable']
        ];
    }
}
