<?php

namespace App\Services\MakeAdmin\Operations;

class PublishMoonshineBuilder extends AbstractMakeOperation
{
    public function handle(int $percent): void
    {
        $this->alert(__('app.build.publishing_moonshine_builder'), $percent);

        $this->runProcess(
            ['php', 'artisan', 'vendor:publish', '--tag=moonshine-builder'],
            'Failed to publish moonshine-builder',
            $this->directories->appProjectDirectory
        );
    }
}
