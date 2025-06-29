<?php

namespace App\Services\MakeAdmin\Operations;

use App\Services\MakeAdmin\ProjectDirectoryDTO;
use Closure;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;

abstract class AbstractMakeOperation
{
    public function __construct(
        protected LoggerInterface $logger,
        protected Closure $alertFunction,
        protected ProjectDirectoryDTO $directories,
    ) {
    }

    /**
     * @param int $percent
     * @return void
     */
    abstract public function handle(int $percent): void;

    protected function alert(string $message, int $percent = 0): void
    {
        \call_user_func($this->alertFunction, $message, $percent);
    }

    /**
     * @param array<string> $command
     * @param string $errorMessage
     * @param string|null $cwd
     * @param array<string, string>|null $env
     * @return void
     */
    protected function runProcess(
        array $command,
        string $errorMessage = 'Command failed',
        ?string $cwd = null,
        ?array $env = null,
    ): void {
        $process = new Process($command, $cwd, $env);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new \RuntimeException($errorMessage . ': ' . $process->getOutput() . '(' . $process->getErrorOutput() . ')');
        }

        $this->logger->debug('Command: ', array_merge($command, [$process->getOutput()]));
    }
}
