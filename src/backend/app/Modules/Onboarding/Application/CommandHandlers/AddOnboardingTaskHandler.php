<?php

namespace App\Modules\Onboarding\Application\CommandHandlers;

use App\Modules\Onboarding\Application\Commands\AddOnboardingTaskCommand;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingPlan\OnboardingPlanId;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingTask\OnboardingTask;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingTask\OnboardingTaskId;
use App\Modules\Onboarding\Domain\Exceptions\OnboardingPlanNotFoundException;
use App\Modules\Onboarding\Domain\Repositories\OnboardingPlanRepositoryInterface;
use App\Modules\Onboarding\Domain\ValueObjects\OwnerType;
use App\Modules\Onboarding\Domain\ValueObjects\TaskType;

class AddOnboardingTaskHandler
{
    public function __construct(
        private readonly OnboardingPlanRepositoryInterface $planRepo,
    ) {}

    public function handle(AddOnboardingTaskCommand $command): OnboardingTask
    {
        $plan = $this->planRepo->findById(OnboardingPlanId::fromString($command->planId));
        if (! $plan) {
            throw new OnboardingPlanNotFoundException($command->planId);
        }

        $task = OnboardingTask::create(
            OnboardingTaskId::generate(), $command->planId, TaskType::Custom,
            OwnerType::from($command->ownerType), $command->ownerId,
            $command->title, $command->description,
            $command->dueDate ? new \DateTimeImmutable($command->dueDate) : null,
            $command->requiresApproval, $command->isPreStart, $command->sortOrder,
        );

        $plan->addTask($task);
        $this->planRepo->save($plan);
        foreach ($plan->popRecordedEvents() as $event) {
            event($event);
        }

        return $task;
    }
}
