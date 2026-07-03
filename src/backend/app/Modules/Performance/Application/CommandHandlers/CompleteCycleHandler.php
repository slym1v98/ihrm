<?php

namespace App\Modules\Performance\Application\CommandHandlers;

use App\Modules\Performance\Application\Commands\CompleteCycleCommand;
use App\Modules\Performance\Domain\Aggregates\PerformanceCycle\PerformanceCycleId;
use App\Modules\Performance\Domain\Repositories\PerformanceCycleRepositoryInterface;
use App\Modules\Performance\Domain\Exceptions\PerformanceCycleNotFoundException;

class CompleteCycleHandler
{
    public function __construct(private readonly PerformanceCycleRepositoryInterface $repo) {}
    public function handle(CompleteCycleCommand $cmd): void
    {
        $id = PerformanceCycleId::fromString($cmd->id);
        $cycle = $this->repo->findById($id) ?? throw new PerformanceCycleNotFoundException($cmd->id);
        $cycle->complete();
        $this->repo->save($cycle);
    }
}
