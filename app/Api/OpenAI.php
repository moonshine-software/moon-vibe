<?php

declare(strict_types=1);

namespace App\Api;

use App\Contracts\SchemaGenerateContract;

class OpenAI implements SchemaGenerateContract
{
    public function generate(array $messages): string
    {
        $result = OpenAI::chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => $messages,
        ]);

        return $result->choices[0]->message->content;
    }
}