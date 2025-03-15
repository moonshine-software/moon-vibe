<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Project;
use App\Services\RequestAdminAi;
use App\Support\SchemaValidator;
use Throwable;

readonly class GenerateFromAI
{
    private int $tries;
    public function __construct(
        private RequestAdminAi $requestAdminAi
    ) {
        $this->tries = 3;
    }

    public function handle(string $projectName, string $promt, int $userId): int
    {
        $project = Project::query()->create([
            'name' => $projectName,
            'description' => $promt,
            'moonshine_user_id' => $userId
        ]);

        $mainPromt = file_get_contents(base_path('promt.md'));

        $messages = [
            ['role' => 'system', 'content' => $mainPromt],
            ['role' => 'user', 'content' => $promt]
        ];

        $isValidSchema = true;
        $tries = 0;

        $error = '';

        do {
            $tries++;

            $schema = $this->requestAdminAi->send($messages);
            if(str_starts_with($schema, '```json')) {
                $schema = preg_replace('/^```json\s*/', '', $schema);
                $schema = preg_replace('/\s*```$/', '', $schema);
            }
            if(str_starts_with($schema, '```')) {
                $schema = preg_replace('/^```\s*/', '', $schema);
                $schema = preg_replace('/\s*```$/', '', $schema);
            }

            try {
                (new SchemaValidator($schema))->validate();
            } catch (Throwable $e) {
                $error = $e->getMessage();
                $messages[] = ['role' => 'assistant', 'content' => $schema];

                if(str_contains($schema, '```json')) {
                    $messages[] = ['role' => 'user', 'content' => "Ты допустил ошибку: не нужно оборачивать результат в ```json, исправь"];
                } else {
                    $messages[] = ['role' => 'user', 'content' => "Ты допустил ошибку: $error, не присылай извинений, попробуй повторно сгенерировать схему и прислать её в формате JSON с исправленной ошибкой."];
                }

                logger()->debug('generation error', ['error' => $error, 'try' => $tries, 'schema' => $schema]);

                $isValidSchema = false;

                continue;
            }
        } while($isValidSchema && $tries < $this->tries);

        $project->schemas()->create([
            'error' => $error,
            'schema' => $schema
        ]);

        return (int) $project->id;
    }
}