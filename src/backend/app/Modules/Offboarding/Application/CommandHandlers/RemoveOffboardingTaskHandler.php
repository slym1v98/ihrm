<?php

namespace App\Modules\Offboarding\Application\CommandHandlers;

use App\Modules\Offboarding\Application\Commands\RemoveOffboardingTaskCommand;
use App\Modules\Offboarding\Domain\Aggregates\OffboardingPlan\OffboardingPlanId;
use App\Modules\Offboarding\Domain\Aggregates\OffboardingTask\OffboardingTaskId;
use App\Modules\Offboarding\Domain\Repositories\OffboardingPlanRepositoryInterface;
use App\Modules\Offboarding\Domain\Repositories\OffboardingTaskRepositoryInterface;
use App\Modules\Offboarding\Domain\Exceptions\OffboardingPlanNotFoundException;

class RemoveOffboardingTaskHandler
{
    public function __construct(
        private readonly OffboardingPlanRepositoryInterface $planRepo,
        private readonly OffboardingTaskRepositoryInterface $taskRepo,
    ) {}

    public function handle(RemoveOffboardingTaskCommand $command): void
    {
        $plan = $this->planRepo->findById(OffboardingPlanId::fromString($command->planId));
        if (!$plan) { throw new OffboardingPlanNotFoundException($command->planId); }
        $plan->removeTask($command->taskId);
        $this->taskRepo->delete(OffboardingTaskId::fromString($command->taskId));
        $this->planRepo->save($plan);
    }
}
