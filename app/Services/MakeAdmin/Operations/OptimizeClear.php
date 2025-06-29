<?php

namespace App\Services\MakeAdmin\Operations;

class OptimizeClear extends AbstractMakeOperation
{
    public function handle(int $percent): void
    {
        $this->alert(__('app.build.optimization'), $percent);

        $this->logger->debug('optimizeClear', [$this->directories->appProjectDirectory]);

        $this->runProcess(
            ['php', 'artisan', 'optimize:clear'],
            'Failed to optimize',
            $this->directories->appProjectDirectory
        );
    }
}
