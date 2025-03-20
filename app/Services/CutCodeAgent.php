<?php

declare(strict_types=1);

namespace App\Services;

use App\Api\CutCode;
use App\Contracts\SchemaGenerateContract;

class CutCodeAgent implements SchemaGenerateContract
{
    public function __construct(
        private CutCode $api
    ) {
    }

    public function generate(array $messages, ?string $mode, ?int $schemaId): string
    {
        $message = array_pop($messages);

        logger()->debug('cut code agent', [$mode, $message['content']]);

        $data = [
            'prompt' => $message['content'],
            'mode' => $mode,
            'id' => $schemaId,
        ];
        return $this->api->post($data);
    }
}