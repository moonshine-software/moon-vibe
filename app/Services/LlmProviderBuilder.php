<?php

declare(strict_types=1);

namespace App\Services;

use App\Api\DeepSeek;
use App\Api\OpenaiApi;
use App\Api\OpenRouterApi;
use App\Contracts\SchemaGenerateContract;
use App\Enums\LlmProvider;
use App\Exceptions\GenerateException;

class LlmProviderBuilder
{
    /** @throws GenerateException */
    public function getProviderApi(int $llm, string $model): SchemaGenerateContract
    {
        return match ($llm) {
            LlmProvider::OPEN_AI->value => new OpenaiApi($model),
            LlmProvider::DEEP_SEEK->value => new DeepSeek($model, config(LlmProvider::DEEP_SEEK->configTokenKey())),
            LlmProvider::OPEN_ROUTER->value => new OpenRouterApi($model, config(LlmProvider::OPEN_ROUTER->configTokenKey())),
            default => throw new GenerateException('Not found LLM for generation')
        };
    }
}
