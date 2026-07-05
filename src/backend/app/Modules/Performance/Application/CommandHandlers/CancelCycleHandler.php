<?php

namespace App\Modules\Performance\Application\CommandHandlers;

use App\Modules\Performance\Application\Commands\CancelCycleCommand;
use App\Modules\Performance\Domain\Aggregates\PerformanceCycle\PerformanceCycleId;
use App\Modules\Performance\Domain\Exceptions\PerformanceCycleNotFoundException;
use App\Modules\Performance\Domain\Repositories\PerformanceCycleRepositoryInterface;

class CancelCycleHandler
{
    public function __construct(private readonly PerformanceCycleRepositoryInterface $repo) {}

    public function handle(CancelCycleCommand $cmd): void
    {
        $id = PerformanceCycleId::fromString($cmd->id);
        $cycle = $this->repo->findById($id) ?? throw new PerformanceCycleNotFoundException($cmd->id);
        $cycle->cancel();
        $this->repo->save($cycle);
    }
}
