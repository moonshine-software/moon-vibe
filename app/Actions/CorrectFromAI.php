<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\SchemaStatus;
use App\Exceptions\GenerateException;
use App\Jobs\CorrectSchemaJob;
use App\Models\MoonShineUser;
use App\Models\ProjectSchema;

readonly class CorrectFromAI
{
    public function __construct(
        private ValidatePendingSchemas $validatePendingSchemas
    ) {

    }

    /**
     * @throws GenerateException
     */
    public function handle(int $schemaId, string $prompt, MoonShineUser $user, string $lang): void
    {
        if ($this->validatePendingSchemas->isPending($user->id)) {
            throw new GenerateException(__('app.schema.already_pending'));
        }

        $schema = ProjectSchema::query()->where('id', $schemaId)->with('project')->first();
        if ($schema === null) {
            throw new GenerateException(__('app.schema.schema_not_found'));
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
