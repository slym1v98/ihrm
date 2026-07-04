<?php

namespace App\Modules\Workflow\Application\CommandHandlers;

use App\Modules\Workflow\Application\Commands\RevokeWorkflowDelegationCommand;
use App\Modules\Workflow\Domain\Aggregates\WorkflowDelegation\WorkflowDelegationId;
use App\Modules\Workflow\Domain\Exceptions\WorkflowDelegationNotFoundException;
use App\Modules\Workflow\Domain\Repositories\WorkflowDelegationRepositoryInterface;

final readonly class RevokeWorkflowDelegationHandler
{
    public function __construct(private WorkflowDelegationRepositoryInterface $delegations) {}

    public function handle(RevokeWorkflowDelegationCommand $command): void
    {
        $delegation = $this->delegations->findById(new WorkflowDelegationId($command->id));
        if ($delegation === null) {
            throw new WorkflowDelegationNotFoundException('Không tìm thấy ủy quyền');
        }
        $delegation->revoke();
        $this->delegations->save($delegation);
    }
}
