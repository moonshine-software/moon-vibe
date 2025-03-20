<?php

declare(strict_types=1);

namespace App\Api;

use App\Contracts\SchemaGenerateContract;
use OpenAI\Laravel\Facades\OpenAI;

class OpenaiApi implements SchemaGenerateContract
{
    public function generate(array $messages, ?string $mode, ?int $schemaId): string
    {
        $result = OpenAi::chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => $messages,
        ]);

        return $result->choices[0]->message->content;
    }
}