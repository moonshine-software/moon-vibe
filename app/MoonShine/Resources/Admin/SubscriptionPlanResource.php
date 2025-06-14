<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Admin;

use App\Enums\Role;
use App\Enums\SubscriptionPeriod;
use App\Models\SubscriptionPlan;
use MoonShine\Laravel\Enums\Action;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\Enum;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @extends ModelResource<SubscriptionPlan, IndexPage, FormPage, DetailPage>
 */
class SubscriptionPlanResource extends ModelResource
{
    protected string $model = SubscriptionPlan::class;

    public function getTitle(): string
    {
        return 'Subscriptions';
    }

    protected function activeActions(): ListOf
    {
        if(auth('moonshine')->user()?->moonshine_user_role_id !== Role::ADMIN) {
            throw new NotFoundHttpException();
        }
        return parent::activeActions();
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
