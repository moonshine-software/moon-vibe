<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Enums\SubscriptionPeriod;
use App\Models\SubscriptionPlan;

use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\Enum;
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
        return 'Subscriptions';
    }

    public function indexFields(): iterable
    {
        return [
			ID::make('id'),
			Text::make('Name', 'name'),
			Number::make('Generation limit', 'generations_limit'),
			Enum::make('Period', 'period')->attach(SubscriptionPeriod::class),
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

    public function rules(mixed $item): array
    {
        return [
			'name' => ['string', 'required'],
			'generations_limit' => ['int', 'required'],
			'period' => ['string', 'required'],
        ];
    }
}
