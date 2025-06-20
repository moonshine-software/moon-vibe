<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SubscriptionPeriod;
use App\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SubscriptionPlan>
 */
class SubscriptionPlanFactory extends Factory
{
    protected $model = SubscriptionPlan::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'generations_limit' => fake()->numberBetween(10, 100),
            'period' => SubscriptionPeriod::MONTHLY->value,
        ];
    }
}