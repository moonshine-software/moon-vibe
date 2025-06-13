<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\ProjectSchema;

class ProjectRepository
{
    public function getSchema(int $id): ?ProjectSchema
    {
        return ProjectSchema::query()->where('id', $id)->with(['project'])->first();
    }
}