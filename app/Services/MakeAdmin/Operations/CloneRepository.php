<?php

namespace App\Services\MakeAdmin\Operations;

class CloneRepository extends AbstractMakeOperation
{
    private string $repository;

    public function setRepository(string $repository): self
    {
        $this->repository = $repository;

        return $this;
    }

    public function handle(int $percent): void
    {
        $this->alert(__('app.build.cloning_repository'), $percent);
        
        $this->runProcess(
            ['git', 'clone', $this->repository, $this->directories->appProjectDirectory],
            'Failed to clone repository'
        );
    }
}
