<?php

namespace App\Services\MakeAdmin\Operations;

class PrepareDirectories extends AbstractMakeOperation
{
    public function handle(int $percent): void
    {
        if(! is_dir($this->directories->path)) {
            return;
        }

        $this->runProcess(
            ['rm', '-rf', $this->directories->path],
            'Failed to remove directory'
        );

        if(! is_dir($this->directories->path)) {
            mkdir($this->directories->path, recursive: true);
        }

        if(! is_dir($this->directories->schemaDirectory)) {
            mkdir($this->directories->schemaDirectory);
        }

        if(! is_dir($this->directories->appProjectDirectory)) {
            mkdir($this->directories->appProjectDirectory, recursive: true);
        }
    }
}
