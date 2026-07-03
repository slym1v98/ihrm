<?php

namespace App\Modules\Performance\Application\CommandHandlers;

use App\Modules\Performance\Application\Commands\UpdateCycleCommand;
use App\Modules\Performance\Domain\Aggregates\PerformanceCycle\PerformanceCycle;
use App\Modules\Performance\Domain\Aggregates\PerformanceCycle\PerformanceCycleId;
use App\Modules\Performance\Domain\Repositories\PerformanceCycleRepositoryInterface;
use App\Modules\Performance\Domain\Exceptions\PerformanceCycleNotFoundException;

class UpdateCycleHandler
{
    public function __construct(private readonly PerformanceCycleRepositoryInterface $repo) {}
    public function handle(UpdateCycleCommand $cmd): PerformanceCycle
    {
        $id = PerformanceCycleId::fromString($cmd->id);
        $cycle = $this->repo->findById($id) ?? throw new PerformanceCycleNotFoundException($cmd->id);
        $cycle->update($cmd->code, $cmd->name, $cmd->description, new \DateTimeImmutable($cmd->startDate), new \DateTimeImmutable($cmd->endDate), $cmd->scoringRules);
        $this->repo->save($cycle);
        return $cycle;
    }
}
