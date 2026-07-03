<?php

namespace App\Modules\Onboarding\Application\CommandHandlers;

use App\Modules\Onboarding\Application\Commands\RemoveOnboardingTaskCommand;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingPlan\OnboardingPlanId;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingTask\OnboardingTaskId;
use App\Modules\Onboarding\Domain\Repositories\OnboardingPlanRepositoryInterface;
use App\Modules\Onboarding\Domain\Repositories\OnboardingTaskRepositoryInterface;
use App\Modules\Onboarding\Domain\Exceptions\OnboardingPlanNotFoundException;

class RemoveOnboardingTaskHandler
{
    public function __construct(
        private readonly OnboardingPlanRepositoryInterface $planRepo,
        private readonly OnboardingTaskRepositoryInterface $taskRepo,
    ) {}

    public function handle(RemoveOnboardingTaskCommand $command): void
    {
        $plan = $this->planRepo->findById(OnboardingPlanId::fromString($command->planId));
        if (!$plan) { throw new OnboardingPlanNotFoundException($command->planId); }
        $plan->removeTask($command->taskId);
        $this->taskRepo->delete(OnboardingTaskId::fromString($command->taskId));
        $this->planRepo->save($plan);
    }
}
