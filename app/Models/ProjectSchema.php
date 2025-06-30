<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SchemaStatus;
use Carbon\Carbon;
use Database\Factories\ProjectSchemaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $project_id
 * @property ?string $schema
 * @property string $first_prompt
 * @property SchemaStatus $status_id
 * @property ?string $error
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Project $project
 */
class ProjectSchema extends Model
{
    /** @use HasFactory<ProjectSchemaFactory> */
    use HasFactory;

    protected $fillable = [
        'project_id',
        'schema',
        'first_prompt',
        'status_id',
        'error',
    ];

    protected $casts = [
        'status_id' => SchemaStatus::class,
    ];

    /**
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
