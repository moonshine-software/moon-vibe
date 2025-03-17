<?php

declare(strict_types=1);

namespace App\Api;

use Illuminate\Support\Facades\Http;

class DeepSeek
{
    private string $token;

    private string $model;

    private string $url = 'https://api.deepseek.com/chat/completions';

    public function __construct()
    {
        $this->token = "sk-fe05fd67fe1648238f3283c423737691";

        $this->model = 'deepseek-reasoner';
    }

    public function request(array $userPromt): string
    {
        $response = Http::timeout(3600)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json',
            ])->post($this->url, [
                'user_promt' => $userPromt
            ]);

        $result = $response->json();

        logger()->debug('DeepSeek response', [$response->getBody()]);

        return $result['choices'][0]['message']['content'];
    }
}