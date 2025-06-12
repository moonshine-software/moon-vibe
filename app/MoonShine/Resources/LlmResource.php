<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Enums\Llm;
use App\Models\LargeLanguageModel;

use App\Repositories\LlmRepository;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\Enum;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Switcher;

/**
 * @extends ModelResource<LargeLanguageModel>
 */
class LlmResource extends ModelResource
{
    protected string $model = LargeLanguageModel::class;

    public function getTitle(): string
    {
        return 'LLM';
    }

    public function indexFields(): iterable
    {
        return [
			ID::make('id'),
			Enum::make('LLM', 'llm')->attach(Llm::class),
			Text::make('Model', 'model'),
			Switcher::make('Default', 'is_default'),
        ];
    }

    public function formFields(): iterable
    {
        $default = Llm::OPEN_AI->value;
        $llms = (new LlmRepository())->getAvailableLlms();
        if($this->getItem() !== null && isset($llms[$this->getItem()->llm->value])) {
            $default = $this->getItem()->llm->value;
        }

        return [
            Box::make([
                ID::make('id'),
                Select::make('LLM', 'llm')
                    ->options($llms)
                    ->default($default)
                ,
                Text::make('Model', 'model'),
                Switcher::make('Default', 'is_default'),
            ])
        ];
    }

    public function detailFields(): iterable
    {
        return [
            ...$this->indexFields()
        ];
    }

    public function rules(mixed $item): array
    {
        return [
			'llm' => ['int', 'required'],
			'model' => ['string', 'required'],
        ];
    }

    protected function beforeUpdating(mixed $item): mixed
    {
        if((int) request()->input('is_default') === 1) {
            $this->setIsDefaultToFalse();
        }
        return $item;
    }

    public function beforeCreating(mixed $item): mixed
    {
        if((int) request()->input('is_default') === 1) {
            $this->setIsDefaultToFalse();
        }
        return $item;
    }

    private function setIsDefaultToFalse(): void
    {
        LargeLanguageModel::query()
            ->where('is_default', 1)
            ->update(['is_default' => 0]);
    }
}
