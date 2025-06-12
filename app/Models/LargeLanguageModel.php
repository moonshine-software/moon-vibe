<?php

declare(strict_types=1);

namespace App\Models;
use App\Enums\Llm;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property Llm $llm
 * @property string $model
 * @property int $is_default
 */
class LargeLanguageModel extends Model
{
	public $timestamps = false;

    protected $fillable = [
		'llm',
		'model',
		'is_default',
    ];

    public $casts = [
        'llm' => Llm::class,
    ];
}
