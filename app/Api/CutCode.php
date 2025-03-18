<?php

declare(strict_types=1);

namespace App\Api;

use App\Contracts\SchemaGenerateContract;
use Throwable;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;

class CutCode implements SchemaGenerateContract
{
    private string $token;

    private string $model;

    private string $url = 'https://n8n.cutcode99.ru/webhook/89c4613d-c1d3-4461-8cd1-32e1fc29665d';

    public function __construct()
    {
        $this->token = "";

        $this->model = '';
    }

    /**
     * @throws ConnectionException
     */
    public function request(string $userPromt): string
    {
        try {
            $response = Http::timeout(3600)
                ->withHeaders([
                    'Content-Type' => 'application/json'
                ])
                ->post($this->url, [
                    'prompt' => $userPromt
                ]);

            if (! $response->successful()) {
                throw new ConnectionException('Failed to connect to the API: ' . $response->status());
            }

            return $response->body();
        } catch (Throwable $e) {
            throw new ConnectionException('Error connecting to API: ' . $e->getMessage());
        }
    }

    /**
     * @throws ConnectionException
     */
    public function generate(array $messages): string
    {
        return $this->request($messages[1]['content']);
    }
}