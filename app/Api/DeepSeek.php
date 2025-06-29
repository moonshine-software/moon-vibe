<?php

declare(strict_types=1);

namespace App\Api;

use App\Contracts\SchemaGenerateContract;
use App\Exceptions\GenerateException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class DeepSeek implements SchemaGenerateContract
{
    private string $url = 'https://api.deepseek.com/chat/completions';

    public function __construct(
        private string $model,
        private string $token
    ) {
    }

    /**
     * @param list<array{role:string, content:string}> $messages
     *
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
            ])->post($this->url, [
                'model' => $this->model,
                'messages' => $messages,
                'stream' => false,
            ]);

        $result = $response->json();

        if (! isset($result['choices'][0])) {
            logger()->error('DeepSeek error: empty choices', $result);

            throw new GenerateException('DeepSeek error: empty choices');
        }

        if (! isset($result['choices'][0]['message'])) {
            logger()->error('DeepSeek error: empty message', $result);

            throw new GenerateException('DeepSeek error: empty message');
        }

        if (! isset($result['choices'][0]['message']['content'])) {
            logger()->error('DeepSeek error: empty content', $result);

            throw new GenerateException('DeepSeek error: empty content');
        }

        return $result['choices'][0]['message']['content'];
    }
}
