<?php

namespace App\Modules\Offboarding\Application\CommandHandlers;

use App\Modules\Offboarding\Application\Commands\WaiveTaskCommand;
use App\Modules\Offboarding\Domain\Aggregates\OffboardingTask\OffboardingTaskId;
use App\Modules\Offboarding\Domain\Repositories\OffboardingTaskRepositoryInterface;
use App\Modules\Offboarding\Domain\Exceptions\OffboardingTaskNotFoundException;

class WaiveTaskHandler
{
    public function __construct(
        private readonly OffboardingTaskRepositoryInterface $taskRepo,
    ) {}

    public function handle(WaiveTaskCommand $command): void
    {
        $task = $this->taskRepo->findById(OffboardingTaskId::fromString($command->taskId));
        if (!$task) { throw new OffboardingTaskNotFoundException($command->taskId); }
        $task->waive($command->reason);
        $this->taskRepo->save($task);
        foreach ($task->popRecordedEvents() as $event) { event($event); }
    }
}
