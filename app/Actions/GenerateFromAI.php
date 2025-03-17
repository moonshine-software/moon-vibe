<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Project;
use App\Jobs\GenerateSchemaJob;
use App\Enums\SchemaStatus;

readonly class GenerateFromAI
{   
    public function handle(string $projectName, string $promt, int $userId): int
    {
        $project = Project::query()->create([
            'name' => $projectName,
            'description' => $promt,
            'moonshine_user_id' => $userId
        ]);

        $schema = $project->schemas()->create([
            'status_id' => SchemaStatus::PENDING,
            'schema' => null
        ]);

        dispatch(new GenerateSchemaJob($promt, $schema->id));

        return $project->id;
    }
}