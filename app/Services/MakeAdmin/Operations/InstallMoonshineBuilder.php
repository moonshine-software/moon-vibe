<?php

namespace App\Services\MakeAdmin\Operations;

class InstallMoonshineBuilder extends AbstractMakeOperation
{
    public function handle(int $percent): void
    {
        $this->alert(__('app.build.installing_moonshine_builder'), $percent);

        $this->runProcess(
            ['composer', 'require', 'dev-lnk/moonshine-builder', '--dev'],
            'Failed to install moonshine-builder',
            $this->directories->appProjectDirectory
        );
    }
} 