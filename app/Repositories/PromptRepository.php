<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Prompt;

class PromptRepository
{
    public function getAllPrompts(): string
    {
        $prompts = Prompt::query()->select('prompt')->orderBy('order')->get();
        $result = [];
        foreach ($prompts as $prompt) {
            $result[] = $prompt->prompt;
        }
        return implode("\n\n", $result);
    }
}