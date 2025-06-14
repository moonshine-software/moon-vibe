<?php

declare(strict_types=1);

namespace App\Contracts;

interface SchemaGenerateContract
{
    /**
     * @param list<array{role:string, content:string}> $messages
     * @return string
     */
    public function generate(array $messages): string;
}