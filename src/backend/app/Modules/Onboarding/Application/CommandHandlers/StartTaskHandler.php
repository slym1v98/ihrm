<?php

namespace App\Modules\Onboarding\Application\CommandHandlers;

use App\Modules\Onboarding\Application\Commands\StartTaskCommand;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingTask\OnboardingTaskId;
use App\Modules\Onboarding\Domain\Exceptions\OnboardingTaskNotFoundException;
use App\Modules\Onboarding\Domain\Repositories\OnboardingTaskRepositoryInterface;

class StartTaskHandler
{
    public function __construct(
        private readonly OnboardingTaskRepositoryInterface $taskRepo,
    ) {}

    public function handle(StartTaskCommand $command): void
    {
        $task = $this->taskRepo->findById(OnboardingTaskId::fromString($command->taskId));
        if (! $task) {
            throw new OnboardingTaskNotFoundException($command->taskId);
        }
        $task->start();
        $this->taskRepo->save($task);
        foreach ($task->popRecordedEvents() as $event) {
            event($event);
        }
    }
}
