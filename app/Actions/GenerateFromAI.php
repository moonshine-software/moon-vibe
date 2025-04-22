<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Project;
use App\Enums\SchemaStatus;
use App\Models\MoonShineUser;
use App\Jobs\GenerateSchemaJob;
use Illuminate\Support\Facades\DB;
use App\Exceptions\GenerateException;

readonly class GenerateFromAI
{
    /**
     * @throws GenerateException
     */
    public function handle(string $projectName, string $prompt, MoonShineUser $user, string $lang): int
    {
        $results = DB::select('
            SELECT s.id
            FROM project_schemas s
            JOIN projects p ON s.project_id = p.id 
            WHERE p.moonshine_user_id = ? 
            AND s.status_id = ?
        ', [$user->id, SchemaStatus::PENDING->value]);
        
        if (count($results) > 0) {
            throw new GenerateException(__('app.schema.already_pending'));
        }

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