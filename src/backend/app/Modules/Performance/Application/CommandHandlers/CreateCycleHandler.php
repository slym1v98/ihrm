<?php

namespace App\Modules\Performance\Application\CommandHandlers;

use App\Modules\Performance\Application\Commands\CreateCycleCommand;
use App\Modules\Performance\Domain\Aggregates\PerformanceCycle\PerformanceCycle;
use App\Modules\Performance\Domain\Aggregates\PerformanceCycle\PerformanceCycleId;
use App\Modules\Performance\Domain\Repositories\PerformanceCycleRepositoryInterface;

class CreateCycleHandler
{
    public function __construct(private readonly PerformanceCycleRepositoryInterface $repo) {}

    public function handle(CreateCycleCommand $cmd): PerformanceCycle
    {
        $id = PerformanceCycleId::generate();
        $cycle = PerformanceCycle::create($id, $cmd->code, $cmd->name, $cmd->description, new \DateTimeImmutable($cmd->startDate), new \DateTimeImmutable($cmd->endDate), $cmd->scoringRules);
        $this->repo->save($cycle);

        return $cycle;
    }
}
