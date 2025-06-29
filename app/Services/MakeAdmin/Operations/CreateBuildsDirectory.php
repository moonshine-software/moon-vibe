<?php

namespace App\Services\MakeAdmin\Operations;

class CreateBuildsDirectory extends AbstractMakeOperation
{
    public function handle(int $percent): void
    {
        $this->alert(__('app.build.creating_builds_directory'), $percent);

        $this->runProcess(
            ['mkdir', '-p', $this->directories->appProjectDirectory . '/builds'],
            'Failed to create builds directory'
        );
    }
}
