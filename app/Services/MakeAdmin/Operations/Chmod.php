<?php

namespace App\Services\MakeAdmin\Operations;

class Chmod extends AbstractMakeOperation
{
    public function handle(int $percent): void
    {
        $this->alert(__('app.build.chmod'), $percent);

        $this->runProcess(
            ['chmod', '-R', '775', $this->directories->appProjectDirectory . '/storage'],
            'Failed chmod',
            $this->directories->appProjectDirectory
        );
    }
}
