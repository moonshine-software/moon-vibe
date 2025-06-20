<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\LlmProvider;
use App\Models\LargeLanguageModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LargeLanguageModel>
 */
class LargeLanguageModelFactory extends Factory
{
    protected $model = LargeLanguageModel::class;

    public function definition(): array
    {
        return [
            'provider' => fake()->randomElement(LlmProvider::cases())->value,
            'model' => fake()->randomElement(['gpt-4', 'gpt-3.5-turbo', 'deepseek-chat']),
            'is_default' => false,
        ];
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }
}
