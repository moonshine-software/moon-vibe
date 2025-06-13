<?php

namespace App\Jobs;

use App\Contracts\SchemaGenerateContract;
use App\Services\LlmProviderBuilder;
use App\Support\ChangeLocale;
use App\Support\Traits\GenerateSchemaTrait;
use Throwable;
use App\Models\ProjectSchema;
use App\Support\SchemaValidator;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class CorrectSchemaJob implements ShouldQueue
{
    use Queueable;

    use GenerateSchemaTrait;

    public function __construct(
        private readonly string $prompt,
        private readonly int $schemaId,
        private readonly int $generateTries,
        private readonly string $lang,
    ) {

    }

    public function handle(): void
    {
        ChangeLocale::set($this->lang, isSetCookie: false);

        $schema = ProjectSchema::query()->where('id', $this->schemaId)->first();
        if($schema === null) {
            return;
        }

        $schemaResult = null;

        try {
            $api = LlmProviderBuilder::getProviderApi($schema->project->llm->provider->value, $schema->project->llm->model);

            $mainPrompt = file_get_contents(base_path('prompt.md'));

            $mainPrompt = "# " . __('app.schema.correction') . PHP_EOL . $mainPrompt;

            $messages = [
                ['role' => 'system', 'content' => $mainPrompt],
                ['role' => 'user', 'content' => $schema->first_prompt],
                ['role' => 'assistant', 'content' => $schema->schema],
                ['role' => 'user', 'content' => $this->prompt],
            ];

            $tries = 0;
            do {
                $tries++;
                $isValidSchema = true;

                $event = $tries === 1
                    ? __('app.schema.correct_job')
                    : __('app.schema.correct_job_attempt', ['tries' => $tries])
                ;

                $this->sendEvent($event, (int) $schema->id);
                $schemaResult = $api->generate($messages);

                $schemaResult = $this->correctSchemaFormat($schemaResult);

                $this->sendEvent("валидация ответа", (int) $schema->id);
                $error = (new SchemaValidator($schemaResult))->validate();

                if($error !== '') {
                    logger()->debug('generation error', [
                            'error'  => $error,
                            'try'    => $tries,
                            'schema' => $schemaResult
                        ]
                    );

                    $messages[] = [
                        'role'    => 'assistant',
                        'content' => $schemaResult
                    ];
                    $messages[] = [
                        'role'    => 'user',
                        'content' => "Ты допустил ошибку: $error, не присылай извинений, попробуй повторно сгенерировать схему и прислать её в формате JSON с исправленной ошибкой."
                    ];

                    $isValidSchema = false;
                }
            } while ($isValidSchema === false && $tries < $this->generateTries);

            $this->saveSchema($schema, $error, $schemaResult);

        } catch (Throwable $e) {
            $this->schemaError($e, $schema, $schemaResult);
        }
    }

    public function failed(Throwable $e): void
    {
        $this->failedJob($e, $this->schemaId);
    }
}
