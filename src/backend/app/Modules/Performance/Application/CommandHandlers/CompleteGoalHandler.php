<?php

namespace App\Modules\Performance\Application\CommandHandlers;

use App\Modules\Performance\Application\Commands\CompleteGoalCommand;
use App\Modules\Performance\Domain\Aggregates\Goal\GoalId;
use App\Modules\Performance\Domain\Exceptions\GoalNotFoundException;
use App\Modules\Performance\Domain\Repositories\GoalRepositoryInterface;

class CompleteGoalHandler
{
    public function __construct(private readonly GoalRepositoryInterface $repo) {}

    public function handle(CompleteGoalCommand $cmd): void
    {
        $goal = $this->repo->findById(GoalId::fromString($cmd->id)) ?? throw new GoalNotFoundException($cmd->id);
        $goal->complete($cmd->actualValue);
        $this->repo->save($goal);
    }
}
