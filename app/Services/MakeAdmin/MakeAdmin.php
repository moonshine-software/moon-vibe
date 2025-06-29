<?php

declare(strict_types=1);

namespace App\Services\MakeAdmin;

use App\Services\MakeAdmin\Operations\{
    AbstractMakeOperation,
    ArchiveDirectory,
    BuildAdmin,
    Chmod,
    CloneRepository,
    CopyBuildFile,
    CopyEnv,
    CreateBuildsDirectory,
    InstallDependencies,
    InstallMarkdown,
    InstallMoonshineBuilder,
    InstallTinyMce,
    MigrateFresh,
    Optimize,
    OptimizeClear,
    PrepareDirectories,
    PublishMoonshineBuilder,
    RemoveAdminDirectory,
    RemoveGit,
    RemoveVendorDirectory,
    SetTestSettings
};
use Closure;
use Psr\Log\LoggerInterface;

readonly class MakeAdmin
{
    private ProjectDirectoryDTO $directories;

    public function __construct(
        private string $schema,
        private string $path,
        private LoggerInterface $logger,
        private Closure $alertFunction,
        private ?string $appProjectDirectory = null,
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

    private function initDirs(): void
    {
        new PrepareDirectories($this->logger, $this->alertFunction, $this->directories)->handle(0);

        file_put_contents($this->directories->schemaFile, $this->schema);
    }

    public function handleForDownload(string $buildRepository): string
    {
        /** @var array{LoggerInterface, Closure, ProjectDirectoryDTO} $operationValues */
        $operationValues = [
            $this->logger,
            $this->alertFunction,
            $this->directories,
        ];

        /** @var list<AbstractMakeOperation> $operations */
        $operations = [
            new CloneRepository(...$operationValues)->setRepository($buildRepository),
            new InstallDependencies(...$operationValues),
            new InstallMoonshineBuilder(...$operationValues),
            new InstallMarkdown(...$operationValues),
            new InstallTinyMce(...$operationValues),
            new PublishMoonshineBuilder(...$operationValues),
            new CreateBuildsDirectory(...$operationValues),
            new CopyBuildFile(...$operationValues),
            new BuildAdmin(...$operationValues),
            new Optimize(...$operationValues),
            new RemoveVendorDirectory(...$operationValues),
            new ArchiveDirectory(...$operationValues),
        ];

        $totalOperations = count($operations);
        for ($i = 0; $i < $totalOperations; $i++) {
            $operations[$i]->handle((int) round($i * 100 / $totalOperations));
        }

        new RemoveAdminDirectory(...$operationValues)->handle(100);

        return $this->directories->appArchiveFile;
    }

    public function handleForTest(string $buildRepository): string
    {
        /** @var array{LoggerInterface, Closure, ProjectDirectoryDTO} $operationValues */
        $operationValues = [
            $this->logger,
            $this->alertFunction,
            $this->directories,
        ];

        /** @var list<AbstractMakeOperation> $operations */
        $operations = [
            new RemoveAdminDirectory(...$operationValues),
            new CloneRepository(...$operationValues)->setRepository($buildRepository),
            new CopyEnv(...$operationValues),
            new SetTestSettings(...$operationValues),
            new InstallDependencies(...$operationValues),
            new InstallMoonshineBuilder(...$operationValues),
            new InstallMarkdown(...$operationValues),
            new InstallTinyMce(...$operationValues),
            new PublishMoonshineBuilder(...$operationValues),
            new CreateBuildsDirectory(...$operationValues),
            new CopyBuildFile(...$operationValues),
            new BuildAdmin(...$operationValues),
            new Optimize(...$operationValues),
            new OptimizeClear(...$operationValues),
            new MigrateFresh(...$operationValues),
            new Chmod(...$operationValues),
            new RemoveGit(...$operationValues),
        ];

        $totalOperations = count($operations);
        for ($i = 0; $i < $totalOperations; $i++) {
            $operations[$i]->handle((int) round($i * 100 / $totalOperations));
        }

        return config('app.url') . '/generate';
    }
}
