<?php

namespace App\Modules\Offboarding\Application\CommandHandlers;

use App\Modules\Offboarding\Application\Commands\AddOffboardingTaskCommand;
use App\Modules\Offboarding\Domain\Aggregates\OffboardingPlan\OffboardingPlanId;
use App\Modules\Offboarding\Domain\Aggregates\OffboardingTask\OffboardingTask;
use App\Modules\Offboarding\Domain\Aggregates\OffboardingTask\OffboardingTaskId;
use App\Modules\Offboarding\Domain\Exceptions\OffboardingPlanNotFoundException;
use App\Modules\Offboarding\Domain\Repositories\OffboardingPlanRepositoryInterface;
use App\Modules\Offboarding\Domain\ValueObjects\OwnerType;
use App\Modules\Offboarding\Domain\ValueObjects\TaskType;

class AddOffboardingTaskHandler
{
    public function __construct(
        private readonly OffboardingPlanRepositoryInterface $planRepo,
    ) {}

    public function handle(AddOffboardingTaskCommand $command): OffboardingTask
    {
        $plan = $this->planRepo->findById(OffboardingPlanId::fromString($command->planId));
        if (! $plan) {
            throw new OffboardingPlanNotFoundException($command->planId);
        }

        $task = OffboardingTask::create(
            OffboardingTaskId::generate(), $command->planId, TaskType::Custom,
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
