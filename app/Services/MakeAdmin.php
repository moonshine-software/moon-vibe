<?php

declare(strict_types=1);

namespace App\Services;

use Symfony\Component\Process\Process;

readonly class MakeAdmin
{
    private string $adminPath;

    public function __construct(
        private string $file
    ) {
        $this->adminPath = base_path('admin');
    }

    public function handle(): string
    {
        $path = $this->createAdminDirectory()
            ->cloneRepository()
            ->installDependencies()
            ->installMoonshineBuilder()
            ->publishMoonshineBuilder()
            ->createBuildsDirectory()
            ->copyBuildFile()
            ->buildAdmin()
            ->optimize()
            ->removeVendorDirectory()
            ->archiveDirectory()
        ;

        $this->removeAdminDirectory();

        return $path;
    }

    private function runProcess(array $command, string $errorMessage = 'Command failed', ?string $cwd = null): self
    {
        $process = new Process($command, $cwd);
        $process->run();
        
        if (!$process->isSuccessful()) {
            throw new \RuntimeException($errorMessage . ': ' . $process->getErrorOutput());
        }
        
        return $this;
    }

    private function createAdminDirectory(): self
    {
        return $this->runProcess(
            ['mkdir', '-p', $this->adminPath],
            'Failed to create admin directory'
        );
    }

    private function cloneRepository(): self
    {
        // TODO github config
        return $this->runProcess(
            ['git', 'clone', 'https://github.com/dev-lnk/moonshine-blank.git', $this->adminPath],
            'Failed to clone repository'
        );
    }

    private function installDependencies(): self
    {
        return $this->runProcess(
            ['composer', 'install'],
            'Failed to install dependencies',
            $this->adminPath
        );
    }

    private function installMoonshineBuilder(): self
    {
        return $this->runProcess(
            ['composer', 'require', 'dev-lnk/moonshine-builder', '--dev'],
            'Failed to install moonshine-builder',
            $this->adminPath
        );
    }

    private function publishMoonshineBuilder(): self
    {
        return $this->runProcess(
            ['php', 'artisan', 'vendor:publish', '--tag=moonshine-builder'],
            'Failed to publish moonshine-builder',
            $this->adminPath
        );
    }

    private function createBuildsDirectory(): self
    {
        return $this->runProcess(
            ['mkdir', '-p', $this->adminPath . '/builds'],
            'Failed to create builds directory'
        );
    }

    private function copyBuildFile(): self
    {
        return $this->runProcess(
            ['cp', $this->file, $this->adminPath . '/builds/'],
            'Failed to copy file'
        );
    }

    private function buildAdmin(): self
    {
        $filename = basename($this->file);
        
        logger()->info('php artisan moonshine:build ' . $filename . ' --json', ['path' => $this->adminPath]);
        
        return $this->runProcess(
            ['php', 'artisan', 'moonshine:build', $filename, '--type=json'],
            'Failed to build JSON',
            $this->adminPath
        );
    }

    private function optimize(): self
    {
        return $this->runProcess(
            ['php', 'artisan', 'optimize'],
            'Failed to optimize',
            $this->adminPath
        );
    }

    private function removeVendorDirectory(): self
    {
        $vendorPath = $this->adminPath . '/vendor';
        if (is_dir($vendorPath)) {
            $this->runProcess(
                ['rm', '-rf', $vendorPath],
                'Failed to remove vendor directory'
            );
        }

        $composerFile = $this->adminPath . '/composer.lock';
        if(is_file($composerFile)) {
            $this->runProcess(
                ['rm',  $composerFile],
                'Failed to remove vendor directory'
            );
        }

        return $this;
    }

    public function archiveDirectory(): string
    {
        //$archivePath = base_path("admins/admin_" . now()->format('Y_m_d_H_i_s') . ".tar.gz");

        // TODO для теста
        $archivePath = base_path("admins/admin.tar.gz");

        $adminDirPath = $this->adminPath;

        $this->runProcess(
            ['tar', '-czf', $archivePath, '-C', dirname($adminDirPath), basename($adminDirPath)],
            'Failed to create tar archive of admin directory'
        );

        if (!file_exists($archivePath)) {
            throw new \RuntimeException('Archive was not created successfully');
        }
    
        return $archivePath;
    }

    private function removeAdminDirectory(): self
    {
        return $this->runProcess(
            ['rm', '-rf', $this->adminPath],
            'Failed to remove admin directory'
        );
    }
}