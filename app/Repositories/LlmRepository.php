<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\LlmProvider;
use App\Models\LargeLanguageModel;
use Illuminate\Support\Collection;

class LlmRepository
{
    /** @return array<int, string> */
    public function getAvailableProviders(): array
    {
        $appLlms = LlmProvider::cases();

        $unsetNotAvailableLlms = function (int $key, LlmProvider $llm) use (&$appLlms
        ): void {
            if (empty(config($llm->configTokenKey()))) {
                unset($appLlms[$key]);
            }
        };

        foreach ($appLlms as $key => $appLlm) {
            $unsetNotAvailableLlms($key, $appLlm);
        }

        if (count($appLlms) === 0) {
            return [];
        }

        $result = [];
        foreach ($appLlms as $appLlm) {
            $result[$appLlm->value] = $appLlm->toString();
        }

        return $result;
    }

    /** @return array<int, string> */
    public function getLlms(): array
    {
        /** @var Collection<int, LargeLanguageModel> $llms */
        $llms = LargeLanguageModel::query()->get();

        $result = [];
        foreach ($llms as $llm) {
            $result[$llm->id] = $llm->getInfo();
        }

        return $result;
    }

    public function getDefaultLlmId(): ?int
    {
        $llm = LargeLanguageModel::query()->where('is_default', 1)->first();
        return $llm === null ? null : $llm->id;
    }
}