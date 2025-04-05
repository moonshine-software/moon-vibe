<?php

namespace App\Models;

use App\Enums\BuildStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Build extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_schema_id',
        'moonshine_user_id',
        'status_id',
        'errors',
        'file_path',
    ];

    protected $casts = [
        'status_id' => BuildStatus::class,
    ];

    public function projectSchema(): BelongsTo
    {
        return $this->belongsTo(ProjectSchema::class, 'project_schema_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(MoonShineUser::class, 'moonshine_user_id');
    }
} 