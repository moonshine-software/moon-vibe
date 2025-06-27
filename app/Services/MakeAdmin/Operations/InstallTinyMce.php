<?php

namespace App\Services\MakeAdmin\Operations;

class InstallTinyMce extends AbstractMakeOperation
{
    public function handle(int $percent): void
    {
        $this->alert(__('app.build.installing_tinymce'), $percent);

        /** @var string $fileContent */
        $fileContent = file_get_contents($this->directories->schemaFile);
        if (!str_contains($fileContent, '"TinyMce"')) {
            return;
        }

        $this->runProcess(
            ['composer', 'require', 'moonshine/tinymce'],
            'Failed to install TinyMce',
            $this->directories->appProjectDirectory
        );
    }
} 