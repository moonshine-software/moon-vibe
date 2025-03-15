<?php

declare(strict_types=1);

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;

class RequestAdminAi
{
    public function send(string $message)
    {
        $promt = file_get_contents(base_path('promt.md'));

        $messages = [
            ['role' => 'system', 'content' => $promt],
            ['role' => 'user', 'content' => $message]
        ];

        $result = \OpenAI\Laravel\Facades\OpenAI::chat()->create([
            //'model' => 'gpt-4o-mini',
            'model' => 'gpt-4',
            'messages' => $messages,
        ]);

        //logger()->debug('gpt-info', json_decode(json_encode($result), true));

        return $result->choices[0]->message->content;
    }
}