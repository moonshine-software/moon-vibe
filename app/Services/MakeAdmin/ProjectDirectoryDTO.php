<?php

declare(strict_types=1);

namespace App\Services\MakeAdmin;

readonly class ProjectDirectoryDTO
{
    public function __construct(
        public string $path,
        public string $schemaDirectory,
        public string $schemaFile,
        public string $appProjectDirectory,
        public string $appArchiveFile,
    ) {
    }
}