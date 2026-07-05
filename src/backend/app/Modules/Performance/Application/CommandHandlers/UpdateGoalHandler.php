<?php

namespace App\Modules\Performance\Application\CommandHandlers;

use App\Modules\Performance\Application\Commands\UpdateGoalCommand;
use App\Modules\Performance\Domain\Aggregates\Goal\GoalId;
use App\Modules\Performance\Domain\Exceptions\GoalNotFoundException;
use App\Modules\Performance\Domain\Repositories\GoalRepositoryInterface;

class UpdateGoalHandler
{
    public function __construct(private readonly GoalRepositoryInterface $repo) {}

    public function handle(UpdateGoalCommand $cmd): void
    {
        $goal = $this->repo->findById(GoalId::fromString($cmd->id)) ?? throw new GoalNotFoundException($cmd->id);
        $goal->update($cmd->title, $cmd->description, $cmd->weight, $cmd->targetValue);
        $this->repo->save($goal);
    }
}
