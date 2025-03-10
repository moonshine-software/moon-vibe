<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class HealthCommand extends Command
{
    protected $signature = 'health';

    protected $description = 'Command description';

    public function handle(): void
    {
        $this->info('App is working!');
    }
}
