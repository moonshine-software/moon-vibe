<?php

namespace App\MoonShine\Components;

use App\Models\Build;
use MoonShine\UI\Components\MoonShineComponent;

/**
 * @phpstan-consistent-constructor
 */
class ProjectBuildComponent extends MoonShineComponent
{
    protected string $view = 'project.build-component';

    public function __construct(
        public readonly ?Build $build,
        public readonly int $buildPercent,
        public readonly string $status,
    ) {
        parent::__construct('build-info');
    }

    public static function fromData(int $userId, ?int $projectId = null): static|string
    {
        $build = Build::query()
            ->where('moonshine_user_id', $userId)
            ->when($projectId, fn($query) => $query
                ->whereHas('projectSchema', fn($query) => $query->where('project_id', $projectId))
            )
            ->first();

        if($build === null) {
            return '';
        }

        return static::fromBuild($build);
    }

    public static function fromBuild(Build $build, ?int $buildPercent = null, ?string $status = null): static
    {
        if($buildPercent === null && $build->file_path !== null) {
            $buildPercent = 100;
        }

        $status = $status ?? $build->status_id->toString();

        return new static($build, $buildPercent ?? 0, $status);
    }
}
