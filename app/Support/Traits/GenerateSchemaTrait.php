<?php

declare(strict_types=1);

namespace App\Support\Traits;

use App\Enums\SchemaStatus;
use App\Models\ProjectSchema;
use MoonShine\Rush\Enums\HtmlReloadAction;
use MoonShine\Rush\Services\Rush;
use MoonShine\Support\AlpineJs;
use MoonShine\Support\Enums\Color;
use MoonShine\Support\Enums\JsEvent;
use MoonShine\UI\Components\Badge;
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
            ? Badge::make(__('app.schema.error') . ': ' . $schema->error, Color::RED)
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

        Rush::events()->js(AlpineJs::event(JsEvent::TABLE_ROW_UPDATED, "schemas-{$schema->id}"));
    }

    private function sendEvent(string $event, int $schemaId): void
    {
        Rush::events()->htmlReload(
            '.schema-id-' . $schemaId,
            (string) Badge::make(__('app.schema.generation') . ': ' .  $event)->customAttributes([
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
        $schema->error = __("moonshine.schema.server_error");
        $schema->save();
        $this->sendEvent(__("moonshine.schema.server_error"), (int) $schema->id);
        report($e);
    }

    private function correctSchemaFormat(string $schema): string
    {
        // Если всё-таки ии не понял, какой формат нужен
        if (str_starts_with($schema, '```json')) {
            $schema = preg_replace('/^```json\s*/', '', $schema);
            $schema = preg_replace('/\s*```$/', '', $schema);
        }
        if (str_starts_with($schema, '```')) {
            $schema = preg_replace('/^```\s*/', '', $schema);
            $schema = preg_replace('/\s*```$/', '', $schema);
        }

        return $schema;
    }
}