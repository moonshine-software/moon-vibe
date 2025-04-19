<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\SubscriptionPlan;

use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Number;

/**
 * @extends ModelResource<SubscriptionPlan>
 */
class SubscriptionPlanResource extends ModelResource
{
    protected string $model = SubscriptionPlan::class;

    public function getTitle(): string
    {
        return 'SubscriptionPlan';
    }

    public function indexFields(): iterable
    {
        // TODO correct labels values
        return [
			ID::make('id'),
			Text::make('name', 'name'),
			Number::make('generations_limit', 'generations_limit'),
			Text::make('reset_period', 'reset_period'),
			Text::make('description', 'description'),
        ];
    }

    public function formFields(): iterable
    {
        return [
            Box::make([
                ...$this->indexFields()
            ])
        ];
    }

    public function detailFields(): iterable
    {
        return [
            ...$this->indexFields()
        ];
    }

    public function filters(): iterable
    {
        return [
        ];
    }

    public function rules(mixed $item): array
    {
        // TODO change it to your own rules
        return [
			'name' => ['string', 'required'],
			'generations_limit' => ['int', 'required'],
			'reset_period' => ['string', 'required'],
			'description' => ['string', 'nullable'],
        ];
    }
}
