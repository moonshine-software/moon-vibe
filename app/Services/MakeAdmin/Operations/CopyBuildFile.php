<?php

namespace App\Services\MakeAdmin\Operations;

class CopyBuildFile extends AbstractMakeOperation
{
    public function handle(int $percent): void
    {
        $this->alert(__('app.build.copying_file'), $percent);

        $this->runProcess(
            ['cp', $this->directories->schemaFile, $this->directories->appProjectDirectory . '/builds/'],
            'Failed to copy file'
        );
    }
} 