<?php

namespace App\Services\MakeAdmin\Operations;

class RemoveVendorDirectory extends AbstractMakeOperation
{
    public function handle(int $percent): void
    {
        $this->alert(__('app.build.removing_vendor_directory'), $percent);

        $vendorPath = $this->directories->appProjectDirectory . '/vendor';
        if (is_dir($vendorPath)) {
            $this->runProcess(
                ['rm', '-rf', $vendorPath],
                'Failed to remove vendor directory'
            );
        }

        $composerFile = $this->directories->appProjectDirectory . '/composer.lock';
        if (is_file($composerFile)) {
            $this->runProcess(
                ['rm', $composerFile],
                'Failed to remove composer.lock'
            );
        }
    }
} 