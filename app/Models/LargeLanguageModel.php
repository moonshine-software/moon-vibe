<?php

declare(strict_types=1);

namespace App\Models;
use App\Enums\LlmProvider;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int         $id
 * @property LlmProvider $provider
 * @property string      $model
 * @property int         $is_default
 */
class LargeLanguageModel extends Model
{
    use HasFactory;

	public $timestamps = false;

    protected $fillable = [
		'provider',
		'model',
		'is_default',
    ];

    public $casts = [
        'provider' => LlmProvider::class,
    ];

    public function getInfo(): string
    {
        return "{$this->provider->toString()} ({$this->model})";
    }
}
