<?php

declare(strict_types=1);

namespace App\Actions;

use App\Exceptions\GenerateException;
use App\Models\Project;
use App\Enums\SchemaStatus;
use App\Models\MoonShineUser;
use App\Jobs\GenerateSchemaJob;

readonly class GenerateFromAI
{
    public function __construct(
        private ValidatePendingSchemas $validatePendingSchemas
    ){
    }

    /**
     * @throws GenerateException
     */
    public function handle(
        string $projectName,
        int $llmId,
        string $prompt,
        MoonShineUser $user,
        string $lang
    ): int {
        if($this->validatePendingSchemas->isPending($user->id)) {
            throw new GenerateException(__('app.schema.already_pending'));
        }

        $project = Project::query()->create([
            'name' => $projectName,
            'llm_id' => $llmId,
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