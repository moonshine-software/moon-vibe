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
            ['role' => 'user', 'content' => $promt],
            ['role' => 'user', 'content' => $message]
        ];

        $result = OpenAI::chat()->create([
            'model' => 'gpt-4',
            'messages' => $messages,
        ]);

        //$result = '{"test" : "test"}';

        logger()->debug('gpt-info', json_decode(json_encode($result), true));

        return $result->choices[0]->message->content;
        //return $result;
    }
}