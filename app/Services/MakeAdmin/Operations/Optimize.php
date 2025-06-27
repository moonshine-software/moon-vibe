<?php

namespace App\Services\MakeAdmin\Operations;

class Optimize extends AbstractMakeOperation
{
    public function handle(int $percent): void
    {
        $this->alert(__('app.build.optimization'), $percent);

        $this->runProcess(
            ['php', 'artisan', 'optimize'],
            'Failed to optimize',
            $this->directories->appProjectDirectory
        );
    }
} 