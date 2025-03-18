<?php

declare(strict_types=1);

namespace App\Services;

use App\Api\CutCode;
use App\Contracts\SchemaGenerateContract;

class CutCodeAgent implements SchemaGenerateContract
{
    private int $schemaId;

    public function __construct(
        private CutCode $api
    ) {
    }

    public function setSchemaId(int $schemaId): self
    {
        $this->schemaId = $schemaId;
        return $this;
    }

    public function generate(array $messages): string
    {
        $data = [
            'prompt' => $messages[1]['content'],
            'mode' => 'gen',
            'id' => $this->schemaId,
        ];
        return $this->api->post($data);
    }

    public function correct(array $messages): string
    {
        $data = [
            'prompt' => $messages[1]['content'],
            'mode' => 'fix',
            'id' => $this->schemaId,
        ];
        return $this->api->post($data);
    }
}