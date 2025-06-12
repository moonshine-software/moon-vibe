<?php

declare(strict_types=1);

namespace App\Enums;

enum Llm: int
{
    case OPEN_AI = 1;

    case DEEP_SEEK = 2;

    public function toString(): string
    {
        return match($this) {
            self::OPEN_AI => 'OpenAI',
            self::DEEP_SEEK => 'DeepSeek',
        };
    }

    public function configTokenKey(): string
    {
        return match($this) {
            self::OPEN_AI => 'openai.api_key',
            self::DEEP_SEEK => 'llm.deep-seek-token',
        };
    }
}