<?php

declare(strict_types=1);

namespace App\Api;

use App\Contracts\SchemaGenerateContract;
use OpenAI\Laravel\Facades\OpenAI;

class OpenaiApi implements SchemaGenerateContract
{
    public function __construct(
        private string $model,
    ) {
    }

    public function generate(array $messages): string
    {
        $result = OpenAi::chat()->create([
            'model' => $this->model,
            'messages' => $messages,
        ]);

        return $result->choices[0]->message->content;
    }
}