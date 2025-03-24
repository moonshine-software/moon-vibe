<?php

declare(strict_types=1);

namespace App\Support\Traits;

use App\Enums\SchemaStatus;
use App\Models\ProjectSchema;
use MoonShine\Rush\Enums\HtmlReloadAction;
use MoonShine\Rush\Services\Rush;
use MoonShine\Support\Enums\Color;
use MoonShine\UI\Components\Badge;
use MoonShine\UI\Fields\Textarea;
use Throwable;

trait GenerateSchemaTrait
{
    private function saveSchema(ProjectSchema $schema, string $error, string $schemaResult): void
    {
        $this->sendEvent("сохранение", (int)$schema->id);

        $status = $error === '' ? SchemaStatus::SUCCESS
            : SchemaStatus::ERROR;

        $schema->status_id = $status;
        $schema->schema = $schemaResult;
        $schema->error = $error !== '' ? $error : null;
        $schema->save();

        $badge = $status === SchemaStatus::ERROR
            ? Badge::make('Ошибка: ' . $schema->error, Color::RED)
                ->customAttributes([
                    'class' => 'schema-id-' . $schema->id
                ])
            : Badge::make(
                $schema->status_id->toString(),
                $schema->status_id->color()
            )->customAttributes([
                'class' => 'schema-id-' . $schema->id
            ]);

        Rush::events()->htmlReload(
            '.schema-id-' . $schema->id,
            (string) $badge,
            HtmlReloadAction::OUTER_HTML
        );

        $textarea = Textarea::make('Json схема', 'schema')
            ->customAttributes([
                'class' => 'schema-edit-id-' . $schema->id,
                'rows'  => 20,
            ])->setValue($schema->schema);
        Rush::events()->htmlReload(
            '.schema-edit-id-' . $schema->id,
            (string) $textarea,
            HtmlReloadAction::OUTER_HTML
        );
    }

    private function sendEvent(string $event, int $schemaId): void
    {
        Rush::events()->htmlReload(
            '.schema-id-' . $schemaId,
            (string) Badge::make('Генерация: ' .  $event)->customAttributes([
                'class' => 'schema-id-' . $schemaId
            ]),
            HtmlReloadAction::OUTER_HTML
        );
    }

    public function failedJob(Throwable $e, int $schemaId): void
    {
        $schema = ProjectSchema::query()->where('id', $schemaId)->first();
        if($schema === null) {
            report($e);
            return;
        }
        $this->schemaError($e, $schema);
    }

    private function schemaError(Throwable $e, ProjectSchema $schema, ?string $schemaResult = null): void
    {
        $schema->status_id = SchemaStatus::ERROR;
        $schema->schema = $schemaResult;
        $schema->error = "Ошибка сервера";
        $schema->save();
        $this->sendEvent("Ошибка сервера", (int) $schema->id);
        report($e);
    }
}