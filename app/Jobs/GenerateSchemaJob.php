<?php

namespace App\Jobs;

use App\Models\ProjectSchema;
use App\Services\GenerateSchemaService;
use App\Support\ChangeLocale;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class GenerateSchemaJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $prompt,
        private readonly int $schemaId,
        private readonly int $generateTries,
        private readonly string $lang,
    ) {

    }

    public function handle(
        ChangeLocale $changeLocale,
        GenerateSchemaService $generateSchemaService,
    ): void {
        $changeLocale->set($this->lang, isSetCookie: false);
        $generateSchemaService->generate($this->prompt, $this->schemaId, $this->generateTries);
    }

    public function failed(Throwable $e): void
    {
        /** @var GenerateSchemaService $generateSchemaService */
        $generateSchemaService = app(GenerateSchemaService::class);

        $schema = ProjectSchema::query()->where('id', $this->schemaId)->first();
        if ($schema === null) {
            report($e);

            return;
        }

        $generateSchemaService->schemaError($e, $schema);
    }
}
