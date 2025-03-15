<?php

declare(strict_types=1);

namespace App\Models;
use App\MoonShine\Resources\ProjectSchemaResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MoonShine\Laravel\Models\MoonshineUser;

class Project extends Model
{
    protected $fillable = [
		'name',
		'description',
		'moonshine_user_id',
    ];

    public function schemas(): HasMany
    {
        return $this->hasMany(ProjectSchema::class, 'project_id');
    }

    public function moonshineUser(): BelongsTo
    {
        return $this->belongsTo(MoonshineUser::class, 'moonshine_user_id');
    }
}
