<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Project;
use App\Enums\SchemaStatus;
use App\Models\MoonShineUser;
use App\Jobs\GenerateSchemaJob;
use App\Exceptions\UserPlanException;

readonly class GenerateFromAI
{   
    public function handle(string $projectName, string $prompt, MoonShineUser $user, string $lang): int
    {
        $project = Project::query()->create([
            'name' => $projectName,
            'description' => $prompt,
            'moonshine_user_id' => $user->id
        ]);

        $schema = $project->schemas()->create([
            'status_id' => SchemaStatus::PENDING,
            'first_prompt' => $prompt,
            'schema' => null
        ]);

        dispatch(new GenerateSchemaJob(
            $prompt,
            $schema->id,
            $user->getGenerationSetting('attempts', 5),
            $lang
        ));

        return $project->id;
    }
}