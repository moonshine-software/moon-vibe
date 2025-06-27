<?php

namespace App\Services\MakeAdmin\Operations;

class BuildAdmin extends AbstractMakeOperation
{
    public function handle(int $percent): void
    {
        $this->alert(__('app.build.building_administrator'), $percent);

        $filename = basename($this->directories->schemaFile);

        $this->runProcess(
            ['php', 'artisan', 'moonshine:build', $filename, '--type=json'],
            'Failed to build JSON',
            $this->directories->appProjectDirectory
        );
    }
} 