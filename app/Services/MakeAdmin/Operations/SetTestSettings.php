<?php

namespace App\Services\MakeAdmin\Operations;

class SetTestSettings extends AbstractMakeOperation
{
    public function handle(int $percent): void
    {
        $this->alert(__('app.build.env_settings'), $percent);

        $env = file_get_contents($this->directories->appProjectDirectory . '/.env');

        if ($env === false) {
            return;
        }

        $env = str_replace('COMPOSE_PROJECT_NAME=moonshine-blank', 'COMPOSE_PROJECT_NAME=moon-vibe', $env);
        $env = str_replace('DB_DATABASE=my_database', 'DB_DATABASE=my_generate', $env);
        $env = str_replace('QUEUE_CONNECTION=redis', 'QUEUE_CONNECTION=sync', $env);
        $env = str_replace('SESSION_DRIVER=redis', 'SESSION_DRIVER=file', $env);

        file_put_contents($this->directories->appProjectDirectory . '/.env', $env);
    }
} 