<?php

namespace App\Services\MakeAdmin\Operations;

class MigrateFresh extends AbstractMakeOperation
{
    public function handle(int $percent): void
    {
        $this->alert(__('app.build.migrate_fresh'), $percent);

        $this->runProcess(
            ['php', 'artisan', 'migrate:fresh', '--seed', '--env=local'],
            'Failed to migrate',
            $this->directories->appProjectDirectory,
            ['DB_DATABASE' => 'my_generate']
        );
    }
}
