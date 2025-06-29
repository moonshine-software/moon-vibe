<?php

namespace App\Services\MakeAdmin\Operations;

class RemoveGit extends AbstractMakeOperation
{
    public function handle(int $percent): void
    {
        $gitPath = $this->directories->appProjectDirectory . '/.git';
        if (is_dir($gitPath)) {
            $this->runProcess(
                ['rm', '-rf', $gitPath],
                'Failed to remove git directory'
            );

            $this->runProcess(
                ['rm', $this->directories->appProjectDirectory . '/.gitignore'],
                'Failed to remove .gitignore'
            );

            $this->runProcess(
                [
                    'cp',
                    $this->directories->appProjectDirectory . '/storage/logs/.gitignore',
                    $this->directories->appProjectDirectory . '/.gitignore',
                ],
                'Failed to copy .gitignore'
            );
        }

        $this->runProcess(
            ['chmod', '-R', '775', $this->directories->appProjectDirectory . '/storage'],
            'Failed chmod',
            $this->directories->appProjectDirectory
        );
    }
}
