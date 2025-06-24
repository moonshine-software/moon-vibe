<?php

declare(strict_types=1);

namespace App\Services\MakeAdmin;

use Closure;
use Symfony\Component\Process\Process;

readonly class MakeAdmin
{
    private ProjectDirectoryDTO $directories;

    public function __construct(
        private string $schema,
        private string $path,
        private ?Closure $alertFunction = null,
        private ?string $appProjectDirectory  = null,
    ) {
        $this->directories = new ProjectDirectoryDTO(
            path: $this->path,
            schemaDirectory: $this->path . '/schema',
            schemaFile: $this->path . '/schema/schema.json',
            appProjectDirectory: $this->appProjectDirectory ?? $this->path . '/app/project',
            appArchiveFile: $this->path . '/app/' . 'project.tar.gz',
        );

        $this->initDirs();
    }

    public function handleForDownload(string $buildRepository): string
    {
        $path = $this
            ->cloneRepository($buildRepository, 4)
            ->installDependencies(7)
            ->installMoonshineBuilder(14)
            ->installMarkdown(21)
            ->installTinyMce(28)
            ->publishMoonshineBuilder(35)
            ->createBuildsDirectory(42)
            ->copyBuildFile(49)
            ->buildAdmin(56)
            ->optimize(63)
            ->removeVendorDirectory(70)
            ->archiveDirectory(77)
        ;

        $this->removeAdminDirectory(90);

        return $path;
    }

    public function handleForTest(string $buildRepository): string
    {
        $this
            ->removeAdminDirectory(1)
            ->cloneRepository($buildRepository, 4)
            ->copyEnv(9)
            ->setTestSettings(14)
            ->installDependencies(21)
            ->installMoonshineBuilder(28)
            ->installMarkdown(35)
            ->installTinyMce(42)
            ->publishMoonshineBuilder(49)
            ->createBuildsDirectory(56)
            ->copyBuildFile(63)
            ->buildAdmin(70)
            ->optimizeClear(77)
            ->migrateFresh(84)
            ->chmod(95)
        ;

        return config('app.url') . '/generate';
    }

    private function copyEnv(int $percent): self
    {
        $this->alert(__('app.build.copy_env'), $percent);

        return $this->runProcess(
            ['cp', $this->directories->appProjectDirectory . '/.env.example', $this->directories->appProjectDirectory . '/.env'],
            'Failed to copy .env.example',
        );
    }

    private function setTestSettings(int $percent): self
    {
        $this->alert(__('app.build.env_settings'), $percent);

        $env = file_get_contents($this->directories->appProjectDirectory . '/.env');

        if($env === false){
            return $this;
        }

        $env = str_replace('COMPOSE_PROJECT_NAME=moonshine-blank', 'COMPOSE_PROJECT_NAME=admin-builder', $env);
        $env = str_replace('DB_DATABASE=my_database', 'DB_DATABASE=my_generate', $env);
        $env = str_replace('QUEUE_CONNECTION=redis', 'QUEUE_CONNECTION=sync', $env);
        $env = str_replace('SESSION_DRIVER=redis', 'SESSION_DRIVER=file', $env);

        file_put_contents($this->directories->appProjectDirectory . '/.env', $env);

        return $this;
    }

    private function migrateFresh(int $percent): self
    {
        $this->alert(__('app.build.migrate_fresh'), $percent);

        return $this->runProcess(
            ['php', 'artisan', 'migrate:fresh', '--seed', '--env=local'],
            'Failed to migrate',
            $this->directories->appProjectDirectory,
            ['DB_DATABASE' => 'my_generate']
        );
    }

    private function chmod(int $percent): self
    {
        $this->alert(__('app.build.chmod'), $percent);

        return $this->runProcess(
            ['chmod', '-R', '775', $this->directories->appProjectDirectory . '/storage'],
            'Failed chmod',
            $this->directories->appProjectDirectory
        );
    }

    /**
     * @param list<string> $command
     * @param string      $errorMessage
     * @param string|null $cwd
     * @param array<string, string> $env
     *
     * @return self
     */
    private function runProcess(array $command, string $errorMessage = 'Command failed', ?string $cwd = null, ?array $env = null): self
    {
        $process = new Process($command, $cwd, $env);
        $process->run();
        
        if (!$process->isSuccessful()) {
            throw new \RuntimeException($errorMessage . ': ' . $process->getOutput()  . '(' . $process->getErrorOutput() . ')');
        }

        logger()->debug('Command: ', array_merge($command, [$process->getOutput()]));

        return $this;
    }

    private function cloneRepository(string $buildRepository, int $percent): self
    {
        $this->alert(__('app.build.cloning_repository'), $percent);
        
        return $this->runProcess(
            ['git', 'clone', $buildRepository, $this->directories->appProjectDirectory],
            'Failed to clone repository'
        );
    }

    private function installDependencies(int $percent): self
    {
        $this->alert(__('app.build.installing_dependencies'), $percent);
    
        return $this->runProcess(
            ['composer', 'install'],
            'Failed to install dependencies',
            $this->directories->appProjectDirectory
        );
    }

    private function installMoonshineBuilder(int $percent): self
    {
        $this->alert(__('app.build.installing_moonshine_builder'), $percent);

        return $this->runProcess(
            ['composer', 'require', 'dev-lnk/moonshine-builder', '--dev'],
            'Failed to install moonshine-builder',
            $this->directories->appProjectDirectory
        );
    }

    private function installMarkdown(int $percent): self
    {
        $this->alert(__('app.build.installing_markdown'), $percent);

        /** @var string $fileContent */
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

    private function installTinyMce(int $percent): self
    {
        $this->alert(__('app.build.installing_tinymce'), $percent);

        /** @var string $fileContent */
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

    private function publishMoonshineBuilder(int $percent): self
    {
        $this->alert(__('app.build.publishing_moonshine_builder'), $percent);

        return $this->runProcess(
            ['php', 'artisan', 'vendor:publish', '--tag=moonshine-builder'],
            'Failed to publish moonshine-builder',
            $this->directories->appProjectDirectory
        );
    }

    private function createBuildsDirectory(int $percent): self
    {
        $this->alert(__('app.build.creating_builds_directory'), $percent);

        return $this->runProcess(
            ['mkdir', '-p', $this->directories->appProjectDirectory . '/builds'],
            'Failed to create builds directory'
        );
    }

    private function copyBuildFile(int $percent): self
    {
        $this->alert(__('app.build.copying_file'), $percent);

        return $this->runProcess(
            ['cp', $this->directories->schemaFile, $this->directories->appProjectDirectory . '/builds/'],
            'Failed to copy file'
        );
    }

    private function buildAdmin(int $percent): self
    {
        $this->alert(__('app.build.building_administrator'), $percent);

        $filename = basename($this->directories->schemaFile);

        return $this->runProcess(
            ['php', 'artisan', 'moonshine:build', $filename, '--type=json'],
            'Failed to build JSON',
            $this->directories->appProjectDirectory
        );
    }

        private function optimize(int $percent): self
    {
        $this->alert(__('app.build.optimization'), $percent);

        return $this->runProcess(
            ['php', 'artisan', 'optimize'],
            'Failed to optimize',
            $this->directories->appProjectDirectory
        );
    }

    private function optimizeClear(int $percent): self
    {
        $this->alert(__('app.build.optimization'), $percent);

        logger()->debug('optimizeClear', [$this->directories->appProjectDirectory]);

        return $this->runProcess(
            ['php', 'artisan', 'optimize:clear'],
            'Failed to optimize',
            $this->directories->appProjectDirectory
        );
    }

    private function removeVendorDirectory(int $percent): self
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
        if(is_file($composerFile)) {
            $this->runProcess(
                ['rm',  $composerFile],
                'Failed to remove vendor directory'
            );
        }

        return $this;
    }

    public function archiveDirectory(int $percent): string
    {
        $this->alert(__('app.build.archiving_directory'), $percent);

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

    private function removeAdminDirectory(int $percent): self
    {
        $this->alert(__('app.build.removing_directory'), $percent);

        return $this->runProcess(
            ['rm', '-rf', $this->directories->appProjectDirectory],
            'Failed to remove admin directory'
        );
    }

    private function alert(string $message, int $percent = 0): void
    {
        if($this->alertFunction) {
            \call_user_func($this->alertFunction, $message, $percent);
        }
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
}