<?php

declare(strict_types=1);

namespace App\Services\MakeAdmin;

use Symfony\Component\Process\Process;

readonly class MakeAdmin
{
    private ProjectDirectoryDTO $directories;

    public function __construct(
        private string $schema,
        private string $path,
    ) {
        $this->directories = new ProjectDirectoryDTO(
            path: $this->path,
            schemaDirectory: $this->path . '/schema',
            schemaFile: $this->path . '/schema/schema.json',
            appProjectDirectory: $this->path . '/app/project',
            appArchiveFile: $this->path . '/app/' . 'project.tar.gz',
        );

        $this->initDirs();
    }

    private function initDirs(): void
    {
        if(is_dir($this->directories->path)) {
            $this->runProcess(
                ['rm', '-rf', $this->directories->path],
                'Failed to remove directory'
            );
        }

        mkdir($this->directories->path, recursive: true);

        if(! is_dir($this->directories->schemaDirectory)) {
            mkdir($this->directories->schemaDirectory);
        }

        if(! is_dir($this->directories->appProjectDirectory)) {
            mkdir($this->directories->appProjectDirectory, recursive: true);
        }

        file_put_contents($this->directories->schemaFile, $this->schema);
    }

    public function handle(): string
    {
        $path = $this
            ->cloneRepository()
            ->installDependencies()
            ->installMoonshineBuilder()
            ->installMarkdown()
            ->installTinyMce()
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
            throw new \RuntimeException($errorMessage . ': ' . $process->getOutput()  . '(' . $process->getErrorOutput() . ')');
        }
        
        return $this;
    }

    private function cloneRepository(): self
    {
        // TODO github config
        return $this->runProcess(
            ['git', 'clone', 'https://github.com/dev-lnk/moonshine-blank.git', $this->directories->appProjectDirectory],
            'Failed to clone repository'
        );
    }

    private function installDependencies(): self
    {
        return $this->runProcess(
            ['composer', 'install'],
            'Failed to install dependencies',
            $this->directories->appProjectDirectory
        );
    }

    private function installMoonshineBuilder(): self
    {
        return $this->runProcess(
            ['composer', 'require', 'dev-lnk/moonshine-builder', '--dev'],
            'Failed to install moonshine-builder',
            $this->directories->appProjectDirectory
        );
    }

    private function installMarkdown(): self
    {
        $fileContent = file_get_contents($this->directories->schemaFile);
        if( ! str_contains($fileContent, '"Markdown"')) {
            return $this;
        }

        return $this->runProcess(
            ['composer', 'require', 'moonshine/easymde'],
            'Failed to install Markdown',
            $this->directories->appProjectDirectory
        );
    }

    private function installTinyMce(): self
    {
        $fileContent = file_get_contents($this->directories->schemaFile);
        if( ! str_contains($fileContent, '"TinyMce"')) {
            return $this;
        }

        return $this->runProcess(
            ['composer', 'require', 'moonshine/tinymce'],
            'Failed to install TinyMce',
            $this->directories->appProjectDirectory
        );
    }

    private function publishMoonshineBuilder(): self
    {
        return $this->runProcess(
            ['php', 'artisan', 'vendor:publish', '--tag=moonshine-builder'],
            'Failed to publish moonshine-builder',
            $this->directories->appProjectDirectory
        );
    }

    private function createBuildsDirectory(): self
    {
        return $this->runProcess(
            ['mkdir', '-p', $this->directories->appProjectDirectory . '/builds'],
            'Failed to create builds directory'
        );
    }

    private function copyBuildFile(): self
    {
        return $this->runProcess(
            ['cp', $this->directories->schemaFile, $this->directories->appProjectDirectory . '/builds/'],
            'Failed to copy file'
        );
    }

    private function buildAdmin(): self
    {
        $filename = basename($this->directories->schemaFile);

        return $this->runProcess(
            ['php', 'artisan', 'moonshine:build', $filename, '--type=json'],
            'Failed to build JSON',
            $this->directories->appProjectDirectory
        );
    }

    private function optimize(): self
    {
        return $this->runProcess(
            ['php', 'artisan', 'optimize'],
            'Failed to optimize',
            $this->directories->appProjectDirectory
        );
    }

    private function removeVendorDirectory(): self
    {
        $vendorPath = $this->directories->appProjectDirectory . '/vendor';
        if (is_dir($vendorPath)) {
            $this->runProcess(
                ['rm', '-rf', $vendorPath],
                'Failed to remove vendor directory'
            );
        }

        $composerFile = $this->directories->appProjectDirectory . '/composer.lock';
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
        $archiveFile = $this->directories->appArchiveFile;

        $adminDirPath = $this->directories->appProjectDirectory;

        $this->runProcess(
            ['tar', '-czf', $archiveFile, '-C', dirname($adminDirPath), basename($adminDirPath)],
            'Failed to create tar archive of admin directory'
        );

        if (!file_exists($archiveFile)) {
            throw new \RuntimeException('Archive was not created successfully');
        }
    
        return $archiveFile;
    }

    private function removeAdminDirectory(): self
    {
        return $this->runProcess(
            ['rm', '-rf', $this->directories->appProjectDirectory],
            'Failed to remove admin directory'
        );
    }
}