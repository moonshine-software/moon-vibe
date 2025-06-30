<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Project;
use App\Models\ProjectSchema;

class ProjectRepository
{
    public function getProject(int $id): ?Project
    {
        return Project::query()->where('id', $id)->first();
    }

    public function getSchema(int $id): ?ProjectSchema
    {
        return ProjectSchema::query()->where('id', $id)->with(['project'])->first();
    }
}
