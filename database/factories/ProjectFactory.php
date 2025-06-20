<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\LargeLanguageModel;
use App\Models\MoonShineUser;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'name' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'moonshine_user_id' => MoonShineUser::factory(),
            'llm_id' => LargeLanguageModel::factory(),
        ];
    }
}
