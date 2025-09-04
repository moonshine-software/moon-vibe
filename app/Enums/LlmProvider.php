<?php

declare(strict_types=1);

namespace App\Enums;

enum LlmProvider: int
{
    case OPEN_AI = 1;

    case DEEP_SEEK = 2;

    case OPEN_ROUTER = 3;

    case CLAUDE = 4;

    public function toString(): string
    {
        return match($this) {
            self::OPEN_AI => 'OpenAI',
            self::DEEP_SEEK => 'DeepSeek',
            self::OPEN_ROUTER => 'OpenRouter',
            self::CLAUDE => 'Claude',
        };
    }

    public function configTokenKey(): string
    {
        return match($this) {
            self::OPEN_AI => 'openai.api_key',
            self::DEEP_SEEK => 'llm.deep-seek-token',
            self::OPEN_ROUTER => 'llm.open-router-token',
            self::CLAUDE => 'llm.claude-api-key',
        };
    }
}
