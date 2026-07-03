<?php

namespace App\Modules\Performance\Application\CommandHandlers;

use App\Modules\Performance\Application\Commands\CreateGoalCommand;
use App\Modules\Performance\Domain\Aggregates\Goal\Goal;
use App\Modules\Performance\Domain\Aggregates\Goal\GoalId;
use App\Modules\Performance\Domain\Repositories\GoalRepositoryInterface;

class CreateGoalHandler
{
    public function __construct(private readonly GoalRepositoryInterface $repo) {}
    public function handle(CreateGoalCommand $cmd): Goal
    {
        $goal = Goal::create(GoalId::generate(), $cmd->cycleId, $cmd->employeeId, $cmd->title, $cmd->description, $cmd->weight, $cmd->targetValue, $cmd->sortOrder);
        $this->repo->save($goal);
        return $goal;
    }
}
