<?php

namespace App\Services\MakeAdmin\Operations;

class InstallDependencies extends AbstractMakeOperation
{
    public function handle(int $percent): void
    {
        $this->alert(__('app.build.installing_dependencies'), $percent);

        $this->runProcess(['composer', 'install'], 'Failed to install dependencies');
    }
}
