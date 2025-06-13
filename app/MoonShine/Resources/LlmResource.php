<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Enums\LlmProvider;
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
            Enum::make('Provider', 'provider')->attach(LlmProvider::class),
            Text::make('Model', 'model'),
            Switcher::make('Default', 'is_default'),
        ];
    }

    public function formFields(): iterable
    {
        $default = LlmProvider::OPEN_AI->value;
        $llms = (new LlmRepository())->getAvailableProviders();
        if($this->getItem() !== null && isset($llms[$this->getItem()->provider->value])) {
            $default = $this->getItem()->provider->value;
        }

        return [
            Box::make([
                ID::make('id'),
                Select::make('Provider', 'provider')
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
			'provider' => ['int', 'required'],
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
