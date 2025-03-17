<?php

declare(strict_types=1);

namespace App\Services;

use App\Api\CutCode;
use App\Api\DeepSeek;
use OpenAI\Laravel\Facades\OpenAI;

class RequestAdminAi
{
    public function send(array $messages): string
    {
//        $result = OpenAI::chat()->create([
//            'model' => 'gpt-3.5-turbo',
//            'messages' => $messages,
//        ]);
//
//        return $result->choices[0]->message->content;

//        $api =  new DeepSeek();
//        return $api->request($messages);

        $api =  new CutCode();
        return $api->request($messages[1]['content']);
    }
}