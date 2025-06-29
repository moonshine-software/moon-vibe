<?php

namespace App\Services\MakeAdmin\Operations;

class ArchiveDirectory extends AbstractMakeOperation
{
    public function handle(int $percent): void
    {
        $this->alert(__('app.build.archiving_directory'), $percent);

        $archiveFile = $this->directories->appArchiveFile;
        $adminDirPath = $this->directories->appProjectDirectory;

        $this->runProcess(
            ['tar', '-czf', $archiveFile, '-C', dirname($adminDirPath), basename($adminDirPath)],
            'Failed to create tar archive of admin directory'
        );

        if (! file_exists($archiveFile)) {
            throw new \RuntimeException('Archive was not created successfully');
        }
    }
}
