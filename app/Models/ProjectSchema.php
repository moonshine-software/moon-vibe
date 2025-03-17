<?php

declare(strict_types=1);

namespace App\Models;
use App\Enums\SchemaStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectSchema extends Model
{
    protected $fillable = [
		'project_id',
		'schema',
        'status_id',
		'error',
    ];

    protected $casts = [
        'status_id' => SchemaStatus::class,
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
