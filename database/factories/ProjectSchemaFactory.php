<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SchemaStatus;
use App\Models\Project;
use App\Models\ProjectSchema;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProjectSchema>
 */
class ProjectSchemaFactory extends Factory
{
    protected $model = ProjectSchema::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'schema' => '{"resources": [{"name": "StageTest", "column": "name", "fields": [{"type": "id", "column": "id", "methods": ["sortable()"]}, {"name": "Title", "type": "string", "column": "name", "hasFilter": true}, {"name": "Order", "type": "integer", "column": "order", "methods": ["sortable()"], "migration": {"methods": ["default(0)"]}}], "menuName": "Stages"}]}',
            'first_prompt' => fake()->paragraph(),
            'status_id' => SchemaStatus::PENDING->value,
            'error' => null,
        ];
    }

    public function withError(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_id' => SchemaStatus::ERROR->value,
            'error' => 'Test error message',
        ]);
    }

    public function successful(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_id' => SchemaStatus::SUCCESS->value,
            'error' => null,
        ]);
    }
}
