<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\SchemaStatus;
use Illuminate\Support\Facades\DB;

class ValidatePendingSchemas
{
    public function isPending(int $userId): bool
    {
        $results = DB::select('
            SELECT s.id
            FROM project_schemas s
            JOIN projects p ON s.project_id = p.id 
            WHERE p.moonshine_user_id = ? 
            AND s.status_id = ?
            LIMIT 1
        ', [$userId, SchemaStatus::PENDING->value]);

        return count($results) > 0;
    }
}