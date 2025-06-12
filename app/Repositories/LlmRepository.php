<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\Llm;

class LlmRepository
{
    /** @return array<int, string> */
    public function getAvailableLlms(): array
    {
        $appLlms = Llm::cases();

        $unsetNotAvailableLlms = function (int $key, Llm $llm) use (&$appLlms): void {
            if(empty(config($llm->configTokenKey()))) {
                unset($appLlms[$key]);
            }
        };

        foreach ($appLlms as $key => $appLlm) {
            $unsetNotAvailableLlms($key, $appLlm);
        }

        if(count($appLlms) === 0) {
            return [];
        }

        $result = [];
        foreach ($appLlms as $appLlm) {
            $result[$appLlm->value] = $appLlm->toString();
        }

        return $result;
    }
}