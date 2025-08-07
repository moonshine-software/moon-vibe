<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SchemaStatus;
use App\Exceptions\BuildException;
use App\Models\ProjectSchema;
use App\Repositories\ProjectRepository;
use App\Repositories\PromptRepository;
use MoonShine\Support\AlpineJs;
use MoonShine\Support\Enums\Color;
use MoonShine\Support\Enums\JsEvent;
use MoonShine\Twirl\Enums\HtmlReloadAction;
use MoonShine\Twirl\Events\TwirlEvent;
use MoonShine\UI\Components\Badge;
use Throwable;

readonly class GenerateSchemaService
{
    public function __construct(
        private ProjectRepository $projectRepository,
        private PromptRepository $promptRepository,
        private LlmProviderBuilder $llmProviderBuilder,
        private SchemaValidator $schemaValidator
    ) {
    }

    public function generate(
        string $prompt,
        int $schemaId,
        int $generateTries,
        bool $isCorrectPrompt = false,
    ): void {
        $schema = $this->projectRepository->getSchema($schemaId);

        if ($schema === null) {
            return;
        }

        $schemaResult = null;

        try {
            if($schema->project->llm === null) {
                throw new BuildException('LLM not found for project');
            }

            $api = $this->llmProviderBuilder->getProviderApi($schema->project->llm->provider->value, $schema->project->llm->model);

            $messages = $this->getMessages($prompt, $schema, $isCorrectPrompt);

            $tries = 1;
            do {
                $isValidSchema = true;

                $event = $tries === 1
                    ? __('app.schema.generate_job')
                    : __('app.schema.generate_job_attempt', ['tries' => $tries])
                ;

                $this->sendEvent($event, (int) $schema->id);
                $schemaResult = $api->generate($messages);

                $schemaResult = $this->correctSchemaFormat($schemaResult);

                $this->sendEvent("Validation of the response", (int) $schema->id);

                $error = $this->schemaValidator->validate($schemaResult);

                if ($error !== '') {
                    logger()->debug(
                        'generation error',
                        [
                            'try' => $tries,
                            'schema' => $schemaResult,
                            'error' => $error,
                        ]
                    );

                    $messages[] = [
                        'role' => 'assistant',
                        'content' => $schemaResult,
                    ];
                    $messages[] = [
                        'role' => 'user',
                        'content' => "You made a mistake:$error, do not send apologies, try to re-generate the scheme and send it in JSON format with a fixed error.",
                    ];

                    $isValidSchema = false;
                }

                $tries++;
            } while ($isValidSchema === false && $tries < $generateTries);

            $this->save($schema, $error, $schemaResult);

        } catch (Throwable $e) {
            $this->schemaError($e, $schema, $schemaResult);
        }
    }

    /**
     * @param string        $prompt
     * @param ProjectSchema $schema
     * @param bool          $isCorrectPrompt
     *
     * @return list<array{role:string, content:string}>
     */
    private function getMessages(string $prompt, ProjectSchema $schema, bool $isCorrectPrompt): array
    {
        $mainPrompt = $this->promptRepository->getAllPrompts();

        if (! $isCorrectPrompt) {
            return [
                ['role' => 'system', 'content' => $mainPrompt],
                ['role' => 'user', 'content' => $prompt],
            ];
        }

        $mainPrompt = "# " . __('app.schema.correction') . PHP_EOL . $mainPrompt;

        return [
            ['role' => 'system', 'content' => $mainPrompt],
            ['role' => 'user', 'content' => $schema->first_prompt],
            ['role' => 'assistant', 'content' => $schema->schema ?? ''],
            ['role' => 'user', 'content' => $prompt],
        ];
    }

    private function save(ProjectSchema $schema, string $error, string $schemaResult): void
    {
        $this->sendEvent("save", $schema->id);

        $status = $error === '' ? SchemaStatus::SUCCESS
            : SchemaStatus::ERROR;

        $schema->status_id = $status;
        $schema->schema = $schemaResult !== '' ? $schemaResult : null;
        $schema->error = $error !== '' ? $error : null;
        $schema->save();

        $badge = $status === SchemaStatus::ERROR
            ? Badge::make(__('app.schema.error') . ': ' . $schema->error, Color::RED)
                ->customAttributes([
                    'class' => 'schema-id-' . $schema->id,
                ])
            : Badge::make(
                $schema->status_id->toString(),
                $schema->status_id->color()
            )->customAttributes([
                'class' => 'schema-id-' . $schema->id,
            ]);

        TwirlEvent::dispatch(
            '.schema-id-' . $schema->id,
            (string) $badge,
            HtmlReloadAction::OUTER_HTML
        );

        TwirlEvent::dispatch(
            'reload-scheme',
            AlpineJs::event(JsEvent::TABLE_ROW_UPDATED, "schemas-{$schema->id}"),
        );
    }

    private function sendEvent(string $event, int $schemaId): void
    {
        TwirlEvent::dispatch(
            '.schema-id-' . $schemaId,
            (string) Badge::make(__('app.schema.generation') . ': ' . $event)->customAttributes([
                'class' => 'schema-id-' . $schemaId,
            ]),
            HtmlReloadAction::OUTER_HTML
        );
    }

    public function schemaError(Throwable $e, ProjectSchema $schema, ?string $schemaResult = null): void
    {
        $schema->status_id = SchemaStatus::ERROR;
        if (! empty($schemaResult)) {
            $schema->schema = $schemaResult;
        }
        $schema->error = $e->getMessage();
        $schema->save();
        $this->sendEvent('Error - ' . $e->getMessage(), $schema->id);
        report($e);
    }

    private function correctSchemaFormat(string $schema): string
    {
        if (str_starts_with($schema, '```json')) {
            $schema = preg_replace('/^```json\s*/', '', $schema);
            $schema = preg_replace('/\s*```$/', '', (string) $schema);
        }
        if (str_starts_with((string) $schema, '```')) {
            $schema = preg_replace('/^```\s*/', '', (string) $schema);
            $schema = preg_replace('/\s*```$/', '', (string) $schema);
        }

        return (string) $schema;
    }
}
