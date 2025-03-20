<?php

declare(strict_types=1);

namespace App\Api;

use App\Contracts\SchemaGenerateContract;
use Illuminate\Support\Facades\Http;

class DeepSeek implements SchemaGenerateContract
{
    private string $token;

    private string $model;

    private string $url = 'https://api.deepseek.com/chat/completions';

    public function __construct()
    {
        $this->token = "sk-fe05fd67fe1648238f3283c423737691";

        $this->model = 'deepseek-reasoner';
    }

    public function request(array $messages): string
    {
        $response = Http::timeout(3600)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json',
            ])->post($this->url, [
                'model' => $this->model,
                'messages' => $messages,
                'stream' => false
            ]);

        $result = $response->json();

        logger()->debug('DeepSeek response', $result);

        return $result['choices'][0]['message']['content'];
    }

    public function generate(array $messages, ?string $mode, ?int $schemaId): string
    {
        return $this->request($messages);
    }
}