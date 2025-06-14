<?php

declare(strict_types=1);

namespace App\Models;
use App\Enums\SubscriptionPeriod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Carbon\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property int $generations_limit
 * @property SubscriptionPeriod $period
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Collection<array-key, MoonShineUser> $users
 */
class SubscriptionPlan extends Model
{
    protected $fillable = [
		'name',
		'generations_limit',
		'period',
    ];

	protected $casts = [
		'period' => SubscriptionPeriod::class,
	];

    /**
     * @return HasMany<MoonShineUser, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(MoonShineUser::class);
    }
}
