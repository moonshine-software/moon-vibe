<?php

declare(strict_types=1);

namespace App\Api;

use App\Contracts\SchemaGenerateContract;
use App\Exceptions\GenerateException;
use OpenAI\Laravel\Facades\OpenAI;

class OpenaiApi implements SchemaGenerateContract
{
    public function __construct(
        private string $model,
    ) {
    }

    /**
     * @throws GenerateException
     */
    public function generate(array $messages): string
    {
        $result = OpenAi::chat()->create([
            'model' => $this->model,
            'messages' => $messages,
        ]);

        if(! isset($result->choices[0])) {
            logger()->error('OpenAPI error: empty choices', [$result]);
            throw new GenerateException('OpenAPI error: empty choices');
        }

        if(! isset($result->choices[0]->message)) {
            logger()->error('OpenAPI error: empty message', [$result]);
            throw new GenerateException('OpenAPI error: empty message');
        }

        if(! isset($result->choices[0]->message->content)) {
            logger()->error('OpenAPI error: empty content', [$result]);
            throw new GenerateException('OpenAPI error: empty content');
        }

        return $result->choices[0]->message->content;
    }
}