<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\SchemaStatus;
use App\Exceptions\GenerateException;
use Illuminate\Support\Facades\DB;

class ValidatePendingSchemas
{
    /**
     * @throws GenerateException
     */
    public function handle(int $userId): void
    {
        $results = DB::select('
            SELECT s.id
            FROM project_schemas s
            JOIN projects p ON s.project_id = p.id 
            WHERE p.moonshine_user_id = ? 
            AND s.status_id = ?
            LIMIT 1
        ', [$userId, SchemaStatus::PENDING->value]);

        if (count($results) > 0) {
            throw new GenerateException(__('app.schema.already_pending'));
        }
    }
}