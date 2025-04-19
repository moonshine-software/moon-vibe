<?php

declare(strict_types=1);

namespace App\Api;

use Throwable;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;

class CutCode
{
    private string $token;

    private string $model;

    private string $url = 'https://n8n.cutcode99.ru/webhook/89c4613d-c1d3-4461-8cd1-32e1fc29665d';
    //private string $url = 'https://n8n.cutcode99.ru/webhook/66debfa5-bf76-4e4f-8d72-f2dbfca2910f';

    public function __construct()
    {
        $this->token = "";

        $this->model = '';
    }

    /**
     * @throws ConnectionException
     */
    public function post(array $data): string
    {
        try {
            $response = Http::timeout(3600)
                ->withHeaders([
                    'Content-Type' => 'application/json'
                ])
                ->post($this->url, $data);

            if (! $response->successful()) {
                throw new ConnectionException('Failed to connect to CutCode API: ' . $response->status());
            }

            return $response->body();
        } catch (Throwable $e) {
            throw new ConnectionException('Error connecting to CutCode API: ' . $e->getMessage());
        }
    }
}