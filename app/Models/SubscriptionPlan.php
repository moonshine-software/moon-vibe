<?php

declare(strict_types=1);

namespace App\Models;
use App\Enums\ResetPeriod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $fillable = [
		'name',
		'generations_limit',
		'reset_period',
    ];

	protected $casts = [
		'reset_period' => ResetPeriod::class,
	];

    public function users(): HasMany
    {
        return $this->hasMany(MoonShineUser::class);
    }
}
