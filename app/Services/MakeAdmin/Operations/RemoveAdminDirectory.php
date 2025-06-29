<?php

namespace App\Services\MakeAdmin\Operations;

class RemoveAdminDirectory extends AbstractMakeOperation
{
    public function handle(int $percent): void
    {
        $this->alert(__('app.build.removing_directory'), $percent);

        $this->runProcess(
            ['rm', '-rf', $this->directories->appProjectDirectory],
            'Failed to remove admin directory'
        );
    }
}
