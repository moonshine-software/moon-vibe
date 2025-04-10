<?php

declare(strict_types=1);

namespace App\Actions;

use App\Jobs\CorrectSchemaJob;
use App\Enums\SchemaStatus;
use App\Models\ProjectSchema;
use MoonShine\Laravel\MoonShineAuth;

readonly class CorrectFromAI
{   
    public function handle(int $schemaId, string $prompt, string $lang): void
    {
        $schema = ProjectSchema::query()->where('id', $schemaId)->with('project')->first();
        if($schema === null) {
            return;
        }

        $schema->status_id = SchemaStatus::PENDING;
        $schema->save();

        $user = MoonShineAuth::getGuard()->user();

        dispatch(new CorrectSchemaJob(
            $prompt,
            $schema->id,
            $user->getGenerationSetting('attempts', 5),
            $lang
        ));
    }
}