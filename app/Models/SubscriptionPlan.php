<?php

declare(strict_types=1);

namespace App\Models;
use App\Enums\SubscriptionPeriod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property int $generations_limit
 * @property SubscriptionPeriod $period
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Collection<MoonShineUser> $users
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

    public function users(): HasMany
    {
        return $this->hasMany(MoonShineUser::class);
    }
}
