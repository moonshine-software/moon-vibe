<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Role;
use App\Models\MoonShineUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MoonShineUser>
 */
class MoonShineUserFactory extends Factory
{
    protected $model = MoonShineUser::class;

    protected static ?string $password = null;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'moonshine_user_role_id' => Role::ADMIN->value,
            'lang' => 'en',
            'generations_used' => 0,
            'avatar' => '',
            'subscription_plan_id' => null,
            'subscription_end_date' => null,
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'moonshine_user_role_id' => Role::ADMIN->value,
        ]);
    }
}
