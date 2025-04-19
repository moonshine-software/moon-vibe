<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\SchemaStatus;
use App\Models\MoonShineUser;
use App\Models\ProjectSchema;
use App\Jobs\CorrectSchemaJob;
use MoonShine\Laravel\MoonShineAuth;
use App\Exceptions\UserPlanException;

readonly class CorrectFromAI
{   
    public function handle(int $schemaId, string $prompt, MoonShineUser $user, string $lang): void
    {
        $schema = ProjectSchema::query()->where('id', $schemaId)->with('project')->first();
        if($schema === null) {
            return;
        }

        $schema->status_id = SchemaStatus::PENDING;
        $schema->save();

        dispatch(new CorrectSchemaJob(
            $prompt,
            $schema->id,
            $user->getGenerationSetting('attempts', 5),
            $lang
        ));
    }
}