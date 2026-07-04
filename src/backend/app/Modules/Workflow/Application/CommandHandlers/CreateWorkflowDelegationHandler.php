<?php

namespace App\Modules\Workflow\Application\CommandHandlers;

use App\Modules\Workflow\Application\Commands\CreateWorkflowDelegationCommand;
use App\Modules\Workflow\Domain\Aggregates\WorkflowDelegation\WorkflowDelegation;
use App\Modules\Workflow\Domain\Aggregates\WorkflowDelegation\WorkflowDelegationId;
use App\Modules\Workflow\Domain\Exceptions\WorkflowDelegationConflictException;
use App\Modules\Workflow\Domain\Repositories\WorkflowDelegationRepositoryInterface;
use Carbon\CarbonImmutable;

final readonly class CreateWorkflowDelegationHandler
{
    public function __construct(private WorkflowDelegationRepositoryInterface $delegations) {}

    public function handle(CreateWorkflowDelegationCommand $command): WorkflowDelegation
    {
        $startAt = CarbonImmutable::parse($command->startAt);
        $endAt = CarbonImmutable::parse($command->endAt);
        if ($this->delegations->hasOverlap($command->delegatorId, $startAt, $endAt, $command->roleType)) {
            throw new WorkflowDelegationConflictException('Khoảng thời gian ủy quyền bị trùng');
        }

        $delegation = new WorkflowDelegation(WorkflowDelegationId::new(), $command->delegatorId, $command->delegateId, $command->roleType, $startAt, $endAt, true, $command->createdBy);
        $this->delegations->save($delegation);

        return $delegation;
    }
}
