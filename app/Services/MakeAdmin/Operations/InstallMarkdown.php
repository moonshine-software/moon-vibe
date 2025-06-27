<?php

namespace App\Services\MakeAdmin\Operations;

class InstallMarkdown extends AbstractMakeOperation
{
    public function handle(int $percent): void
    {
        $this->alert(__('app.build.installing_markdown'), $percent);

        /** @var string $fileContent */
        $fileContent = file_get_contents($this->directories->schemaFile);
        if (!str_contains($fileContent, '"Markdown"')) {
            return;
        }

        $this->runProcess(
            ['composer', 'require', 'moonshine/easymde'],
            'Failed to install Markdown',
            $this->directories->appProjectDirectory
        );
    }
} 