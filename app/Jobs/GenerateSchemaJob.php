<?php

namespace App\Jobs;

use MoonShine\Rush\Enums\HtmlReloadAction;
use MoonShine\Rush\Services\Rush;
use MoonShine\Support\Enums\Color;
use MoonShine\UI\Components\Badge;
use MoonShine\UI\Fields\Textarea;
use Throwable;
use App\Enums\SchemaStatus;
use App\Models\ProjectSchema;
use App\Services\RequestAdminAi;
use App\Support\SchemaValidator;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class GenerateSchemaJob implements ShouldQueue
{
    use Queueable;

    private int $generateTries;

    public function __construct(
        private readonly string $promt,
        private readonly int $schemaId,
    ) {
        // TODO config
        $this->generateTries = 1;
    }

    public function handle(): void
    {
        logger()->debug('start job');

        $schema = ProjectSchema::query()->where('id', $this->schemaId)->first();
        if($schema === null) {
            return;
        }

        logger()->debug('generate job', ['promt' => $this->promt, 'schemaId' => $this->schemaId]);

        $requestAdminAi = new RequestAdminAi();

        $mainPromt = file_get_contents(base_path('promt.md'));

        $messages = [
            ['role' => 'system', 'content' => $mainPromt],
            ['role' => 'user', 'content' => $this->promt]
        ];

        $isValidSchema = true;
        $tries = 0;

        $error = '';

        do {
            $tries++;

            $this->sendEvent("выполнение запроса, попытка $tries...", (int) $schema->id);
            $schemaResult = $requestAdminAi->send($messages);

//            sleep(5);
//            $schemaResult = '{"resources": [{"name": "resource1"}, {"name": "resource2"}]}';

            if(str_starts_with($schemaResult, '```json')) {
                $schemaResult = preg_replace('/^```json\s*/', '', $schemaResult);
                $schemaResult = preg_replace('/\s*```$/', '', $schemaResult);
            }
            if(str_starts_with($schemaResult, '```')) {
                $schemaResult = preg_replace('/^```\s*/', '', $schemaResult);
                $schemaResult = preg_replace('/\s*```$/', '', $schemaResult);
            }

            try {
                $this->sendEvent("валидация ответа", (int) $schema->id);
                (new SchemaValidator($schemaResult))->validate();
            } catch (Throwable $e) {
                $error = $e->getMessage();

                $messages[] = ['role' => 'assistant', 'content' => $schemaResult];
                $messages[] = ['role' => 'user', 'content' => "Ты допустил ошибку: $error, не присылай извинений, попробуй повторно сгенерировать схему и прислать её в формате JSON с исправленной ошибкой."];

                logger()->debug('generation error', ['error' => $error, 'try' => $tries, 'schema' => $schemaResult]);

                $isValidSchema = false;

                continue;
            }
        } while($isValidSchema && $tries < $this->generateTries);

        $this->sendEvent("сохранение", (int) $schema->id);

        $status = $error === '' ? SchemaStatus::SUCCESS : SchemaStatus::ERROR;

        $schema->status_id = $status;
        $schema->schema = $schemaResult;
        $schema->error = $error !== '' ? $error : null;
        $schema->save();

        $badge = $status === SchemaStatus::ERROR
            ? Badge::make('Ошибка: ' . $schema->error, Color::RED)->customAttributes([
                'class' => 'schema-id-' . $schema->id
            ])
            : Badge::make($schema->status_id->toString(), $schema->status_id->color())->customAttributes([
                'class' => 'schema-id-' . $schema->id
            ])
        ;
        Rush::events()->htmlReload('.schema-id-' . $schema->id, (string) $badge, HtmlReloadAction::OUTER_HTML);

        $textarea = Textarea::make('Json схема', 'schema')->customAttributes([
            'class' => 'schema-edit-id-' . $schema->id,
            'rows' => 20,
        ])->setValue($schema->schema);
        Rush::events()->htmlReload('.schema-edit-id-' . $schema->id, (string) $textarea, HtmlReloadAction::OUTER_HTML);
    }

    private function sendEvent(string $event, int $schemaId)
    {
        Rush::events()->htmlReload(
            '.schema-id-' . $schemaId,
            (string) Badge::make('Генерация: ' .  $event)->customAttributes([
                'class' => 'schema-id-' . $schemaId
            ]),
            HtmlReloadAction::OUTER_HTML
        );
    }
}
