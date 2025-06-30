<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use MoonShine\Laravel\Models\MoonshineUser;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $moonshine_user_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property MoonshineUser $moonshineUser
 * @property ?LargeLanguageModel $llm
 * @property Collection<int, ProjectSchema> $schemas
 */
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'llm_id',
        'moonshine_user_id',
    ];

    /**
     * @return HasMany<ProjectSchema, $this>
     */
    public function schemas(): HasMany
    {
        return $this->hasMany(ProjectSchema::class, 'project_id');
    }

    /**
     * @return BelongsTo<MoonshineUser, $this>
     */
    public function moonshineUser(): BelongsTo
    {
        return $this->belongsTo(MoonshineUser::class, 'moonshine_user_id');
    }

    /**
     * @return BelongsTo<LargeLanguageModel, $this>
     */
    public function llm(): BelongsTo
    {
        return $this->belongsTo(LargeLanguageModel::class, 'llm_id');
    }
}
