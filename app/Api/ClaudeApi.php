<?php

declare(strict_types=1);

namespace App\Api;

use App\Contracts\SchemaGenerateContract;
use App\Exceptions\GenerateException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

readonly class ClaudeApi implements SchemaGenerateContract
{
    private string $url;

    public function __construct(
        private string $model,
        private string $token,
    )
    {
        $this->url = 'https://api.anthropic.com/v1/messages';
    }

    /**
     * @param list<array{role: string, content: string}> $messages
     * @return string
     * @throws ConnectionException
     * @throws GenerateException
     */
    public function generate(array $messages): string
    {
        // Check API key format
        if (!str_starts_with($this->token, 'sk-ant-')) {
            logger()->error('Claude API key format error', [
                'token_prefix' => substr($this->token, 0, 10) . '...',
                'expected_prefix' => 'sk-ant-',
            ]);
            throw new GenerateException('Claude API key must start with "sk-ant-"');
        }

        // Claude API requires separate system message and user/assistant messages
        $systemMessage = '';
        $conversationMessages = [];

        foreach ($messages as $message) {
            if ($message['role'] === 'system') {
                $systemMessage = $message['content'];
            } else {
                $conversationMessages[] = $message;
            }
        }

        $requestData = [
            'model' => $this->model,
            'max_tokens' => 4000,
            'messages' => $conversationMessages,
        ];

        if (!empty($systemMessage)) {
            $requestData['system'] = $systemMessage;
        }

        logger()->info('Claude API request', [
            'url' => $this->url,
            'model' => $this->model,
            'token_prefix' => substr($this->token, 0, 15) . '...',
            'token_length' => strlen($this->token),
        ]);

        $response = Http::timeout(3600)
            ->withHeaders([
                'x-api-key' => $this->token,
                'Content-Type' => 'application/json',
                'anthropic-version' => '2023-06-01',
            ])
            ->post($this->url, $requestData);

        logger()->info('Claude API response', [
            'status' => $response->status(),
            'headers' => $response->headers(),
        ]);

        // Check for authentication errors
        if ($response->status() === 401) {
            $result = $response->json();
            logger()->error('Claude authentication error', [
                'status' => $response->status(),
                'response' => $result,
                'token_starts_correctly' => str_starts_with($this->token, 'sk-ant-'),
                'token_length' => strlen($this->token),
            ]);
            throw new GenerateException('Claude authentication error: ' . ($result['error']['message'] ?? 'invalid API key'));
        }

        // Check for other HTTP errors
        if (!$response->successful()) {
            $result = $response->json();
            logger()->error('Claude HTTP error', [
                'status' => $response->status(),
                'response' => $result,
            ]);
            throw new GenerateException('Claude HTTP error: ' . $response->status());
        }

        $result = $response->json();

        if (!isset($result['content'][0])) {
            logger()->error('Claude error: empty content', $result);
            throw new GenerateException('Claude error: empty content');
        }

        if (!isset($result['content'][0]['text'])) {
            logger()->error('Claude error: empty text in content', $result);
            throw new GenerateException('Claude error: empty text in content');
        }

        return $result['content'][0]['text'];
    }
}
