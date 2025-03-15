<?php

declare(strict_types=1);

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectSchema extends Model
{
    protected $fillable = [
		'project_id',
		'schema',
		'error',
    ];

    protected $casts = [
        'schema' => 'json',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
