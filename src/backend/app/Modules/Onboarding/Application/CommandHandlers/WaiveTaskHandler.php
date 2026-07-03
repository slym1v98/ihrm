<?php

namespace App\Modules\Onboarding\Application\CommandHandlers;

use App\Modules\Onboarding\Application\Commands\WaiveTaskCommand;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingTask\OnboardingTaskId;
use App\Modules\Onboarding\Domain\Repositories\OnboardingTaskRepositoryInterface;
use App\Modules\Onboarding\Domain\Exceptions\OnboardingTaskNotFoundException;

class WaiveTaskHandler
{
    public function __construct(
        private readonly OnboardingTaskRepositoryInterface $taskRepo,
    ) {}

    public function handle(WaiveTaskCommand $command): void
    {
        $task = $this->taskRepo->findById(OnboardingTaskId::fromString($command->taskId));
        if (!$task) { throw new OnboardingTaskNotFoundException($command->taskId); }
        $task->waive($command->reason);
        $this->taskRepo->save($task);
        foreach ($task->popRecordedEvents() as $event) { event($event); }
    }
}
