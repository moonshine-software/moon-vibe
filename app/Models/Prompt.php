<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\PromptFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $title
 * @property string $prompt
 * @property int $order
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Prompt extends Model
{
    /** @use HasFactory<PromptFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'prompt',
        'order',
    ];
}
