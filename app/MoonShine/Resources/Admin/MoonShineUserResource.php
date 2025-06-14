<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Admin;

use App\Enums\Role;
use App\Models\MoonShineUser;
use App\Models\SubscriptionPlan;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
use MoonShine\Laravel\Enums\Action;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Models\MoonshineUserRole;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\Order;
use MoonShine\Support\Attributes\Icon;
use MoonShine\Support\Enums\Color;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\Collapse;
use MoonShine\UI\Components\FlexibleRender;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Flex;
use MoonShine\UI\Components\Tabs;
use MoonShine\UI\Components\Tabs\Tab;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\Email;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Password;
use MoonShine\UI\Fields\PasswordRepeat;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Text;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

// @phpstan-ignore-next-line
#[Icon('users')]
#[Group('moonshine::ui.resource.system', 'users', translatable: true)]
#[Order(1)]
/**
 * @extends ModelResource<MoonshineUser>
 */
class MoonShineUserResource extends ModelResource
{
    protected string $model = MoonshineUser::class;

    protected string $column = 'name';

    /** @var string[]  */
    protected array $with = ['moonshineUserRole', 'subscriptionPlan'];

    protected bool $simplePaginate = true;

    protected bool $columnSelection = true;

    public function getTitle(): string
    {
        return __('moonshine::ui.resource.admins_title');
    }

    protected function activeActions(): ListOf
    {
        if(auth('moonshine')->user()->moonshine_user_role_id !== Role::ADMIN) {
            throw new NotFoundHttpException();
        }
        return parent::activeActions()->except(Action::VIEW);
    }

    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),

            BelongsTo::make(
                __('moonshine::ui.resource.role'),
                'moonshineUserRole',
                formatted: static fn (MoonshineUserRole $model) => $model->name,
                resource: MoonShineUserRoleResource::class,
            )->badge(Color::PURPLE),

            Text::make(__('moonshine::ui.resource.name'), 'name'),

            BelongsTo::make(
                'Тип подписки',
                'subscriptionPlan',
                formatted: static fn (SubscriptionPlan $model) => $model->name,
                resource: SubscriptionPlanResource::class,
            ),

            Preview::make('Expiration Date', formatted: static fn (MoonshineUser $model) => $model->subscription_end_date->format('d.m.Y')),

            Email::make(__('moonshine::ui.resource.email'), 'email')
                ->sortable(),
        ];
    }

    protected function detailFields(): iterable
    {
        return $this->indexFields();
    }

    protected function formFields(): iterable
    {
        $expirationField = FlexibleRender::make('');
        if($this->getItem() !== null) {
            $expirationField = Date::make('Expiration Date', 'subscription_end_date');
        }

        return [
            Box::make([
                Tabs::make([
                    Tab::make(__('moonshine::ui.resource.main_information'), [
                        ID::make()->sortable(),

                        BelongsTo::make(
                            'Role',
                            'moonshineUserRole',
                            formatted: static fn (MoonshineUserRole $model) => $model->name,
                            resource: MoonShineUserRoleResource::class,
                        )
                            ->default(MoonshineUserRole::query()->where('id', Role::USER->value)->first()),

                        BelongsTo::make(
                            'Тип подписки',
                            'subscriptionPlan',
                            formatted: static fn (SubscriptionPlan $model) => $model->name,
                            resource: SubscriptionPlanResource::class,
                        ),

                        $expirationField,

                        Flex::make([
                            Text::make(__('moonshine::ui.resource.name'), 'name')
                                ->required(),

                            Email::make(__('moonshine::ui.resource.email'), 'email')
                                ->required(),
                        ]),
                    ])->icon('user-circle'),

                    Tab::make(__('moonshine::ui.resource.password'), [
                        Collapse::make(__('moonshine::ui.resource.change_password'), [
                            Password::make(__('moonshine::ui.resource.password'), 'password')
                                ->customAttributes(['autocomplete' => 'new-password'])
                                ->eye(),

                            PasswordRepeat::make(__('moonshine::ui.resource.repeat_password'), 'password_repeat')
                                ->customAttributes(['autocomplete' => 'confirm-password'])
                                ->eye(),
                        ])->icon('lock-closed'),
                    ])->icon('lock-closed'),
                ]),
            ]),
        ];
    }

    /**
     * @param MoonShineUser $item
     *
     * @return mixed
     */
    protected function afterCreated(mixed $item): mixed
    {
        if(
            request()->post('subscription_plan_id')
            && $item->subscription_end_date === null
        ) {
            $item->subscription_end_date = now()->add("+ {$item->subscriptionPlan->period->getPeriod()}");
            $item->save();
        }

        return $item;
    }

    /**
     * @return array{name: string, moonshine_user_role_id: string, email: array<array-key, string|Unique>, password: string}
     */
    protected function rules($item): array
    {
        return [
            'name' => 'required',
            'moonshine_user_role_id' => 'required',
            'email' => [
                'sometimes',
                'bail',
                'required',
                'email',
                Rule::unique('moonshine_users')->ignoreModel($item),
            ],
            'password' => $item->exists
                ? 'sometimes|nullable|min:6|required_with:password_repeat|same:password_repeat'
                : 'required|min:6|required_with:password_repeat|same:password_repeat',
        ];
    }
}
