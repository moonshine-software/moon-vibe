<?php

declare(strict_types=1);

namespace App\Api;

use App\Contracts\SchemaGenerateContract;
use App\Exceptions\GenerateException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

readonly class OpenRouterApi implements SchemaGenerateContract
{
    private string $url;

    public function __construct(
        private string $model,
        private string $token,
    ) {
        $this->url = 'https://openrouter.ai/api/v1/chat/completions';
    }

    /**
     * @param list<array{role: string, content: string}> $messages
     * @return string
     * @throws ConnectionException
     * @throws GenerateException
     */
    public function generate(array $messages): string
    {
        $response = Http::timeout(3600)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => config('app.url'),
                'X-Title' => config('app.name'),
            ])
            ->post($this->url, [
                'model' => $this->model,
                'messages' => $messages,
                'stream' => false,
            ]);

        $result = $response->json();

        if (!isset($result['choices'][0])) {
            logger()->error('OpenRouter error: empty choices', $result);
            throw new GenerateException('OpenRouter error: empty choices');
        }

        if (!isset($result['choices'][0]['message'])) {
            logger()->error('OpenRouter error: empty message', $result);
            throw new GenerateException('OpenRouter error: empty message');
        }

        if (!isset($result['choices'][0]['message']['content'])) {
            logger()->error('OpenRouter error: empty content', $result);
            throw new GenerateException('OpenRouter error: empty content');
        }

        return $result['choices'][0]['message']['content'];
    }
}
