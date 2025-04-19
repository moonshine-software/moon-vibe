<?php

declare(strict_types=1);

namespace App\Models;
use App\Enums\SubscriptionPeriod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
