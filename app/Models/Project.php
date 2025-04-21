<?php

declare(strict_types=1);

namespace App\Models;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use MoonShine\Laravel\Models\MoonshineUser;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $moonshine_user_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property MoonshineUser $moonshineUser
 * @property Collection<ProjectSchema> $schemas
 */
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
