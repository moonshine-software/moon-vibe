<?php

declare(strict_types=1);

namespace App\Contracts;

interface SchemaGenerateContract
{
    public function generate(array $messages, ?string $mode, ?int $schemaId): string;
}