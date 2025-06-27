<?php

namespace App\Services\MakeAdmin\Operations;

class CopyEnv extends AbstractMakeOperation
{
    public function handle(int $percent): void
    {
        $this->alert(__('app.build.copy_env'), $percent);

        $this->runProcess(
            ['cp', $this->directories->appProjectDirectory . '/.env.example', $this->directories->appProjectDirectory . '/.env'],
            'Failed to copy .env.example'
        );
    }
} 