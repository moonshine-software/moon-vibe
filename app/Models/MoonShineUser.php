<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Role;
use App\Support\ChangeLocale;
use Database\Factories\MoonShineUserFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MoonShine\Laravel\Models\MoonshineUser as BaseMoonShineUser;
use Carbon\Carbon;
use MoonShine\Laravel\Models\MoonshineUserRole;
use MoonShine\Support\Enums\Color;

/**
 * @property int $id
 * @property string $email
 * @property Role $moonshine_user_role_id
 * @property string $password
 * @property string $name
 * @property string $avatar
 * @property array<string, array<string, string>> $settings
 * @property string $lang
 * @property int $subscription_plan_id
 * @property int $generations_used
 * @property Carbon $subscription_end_date
 * @property SubscriptionPlan $subscriptionPlan
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property MoonshineUserRole $role
 */
class MoonShineUser extends BaseMoonShineUser
{
    protected $table = 'moonshine_users';

    protected $rememberTokenName = null;

    protected $fillable = [
        'email',
        'moonshine_user_role_id',
        'password',
        'name',
        'avatar',       
        'settings',
        'lang',
        'subscription_plan_id',
        'generations_used',
        'subscription_end_date',
    ];

    protected $casts = [
        'settings' => 'json',
        'subscription_end_date' => 'date',
        'moonshine_user_role_id' => Role::class,
    ];

    protected static function booted(): void
    {
        static::saving(static function (self $model) {
            if(
                $model->hasAttribute('attempts')
                //&& $model->hasAttribute('repository')
            ) {
                $settings['generation'] = [
                    'attempts' => $model->getAttribute('attempts'),
                ];

                $settings['build'] = [
                    //'repository' => $model->getAttribute('repository')
                    'repository' => 'https://github.com/dev-lnk/moonshine-blank.git'
                ];

                unset($model->attributes['attempts']);
                //unset($model->attributes['repository']);

                $model->settings = $settings;
            }

            if($model->id !== null && auth('moonshine')->user()?->id === $model->id) {
                (new ChangeLocale())->set((string) $model->getAttribute('lang'));
            }
        });
    }

    public function getGenerationSetting(string $key, mixed $default = null): mixed
    {
        if($this->settings === null) {
            return $default;
        }
        if(! isset($this->settings['generation'][$key])) {
            return $default;
        }
        return $this->settings['generation'][$key];
    }

    public function getBuildSetting(string $key, mixed $default = null): mixed
    {
        if($this->settings === null) {
            return $default;
        }
        if(! isset($this->settings['build'][$key])) {
            return $default;
        }
        return $this->settings['build'][$key];
    }

    /**
     * @return BelongsTo<SubscriptionPlan, $this>
     */
    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function getGenerationsLeft(): false|int
    {
        if($this->moonshine_user_role_id === Role::ADMIN) {
            return false;
        }

        if($this->subscription_end_date <= now()) {
            return 0;
        }

        /** @var SubscriptionPlan|null $subscriptionPlan */
        $subscriptionPlan = $this->subscriptionPlan;
        if($subscriptionPlan === null) {
            return 0;
        }
        return $subscriptionPlan->generations_limit - $this->generations_used;
    }

    /**
     * @return array{info: string|numeric-string, color: Color}
     */
    public function getGenerationLeftInfo(): array
    {
        $generationsLeft = $this->getGenerationsLeft();
        return [
            'info' => $generationsLeft === false ? '-' : (string) $generationsLeft,
            'color' => ($generationsLeft === false || $generationsLeft > 0) ? Color::GREEN : Color::RED
        ];
    }

    protected static function newFactory(): MoonShineUserFactory
    {
        return MoonShineUserFactory::new();
    }
}