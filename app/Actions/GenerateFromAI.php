<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Project;
use App\Jobs\GenerateSchemaJob;
use App\Enums\SchemaStatus;
use MoonShine\Laravel\MoonShineAuth;

readonly class GenerateFromAI
{   
    public function handle(string $projectName, string $prompt, int $userId, string $lang): int
    {
        $project = Project::query()->create([
            'name' => $projectName,
            'description' => $prompt,
            'moonshine_user_id' => $userId
        ]);

        $schema = $project->schemas()->create([
            'status_id' => SchemaStatus::PENDING,
            'first_prompt' => $prompt,
            'schema' => null
        ]);

        $user = MoonShineAuth::getGuard()->user();

        dispatch(new GenerateSchemaJob(
            $prompt,
            $schema->id,
            $user->getGenerationSetting('attempts', 5),
            $lang
        ));

        return $project->id;
    }
}