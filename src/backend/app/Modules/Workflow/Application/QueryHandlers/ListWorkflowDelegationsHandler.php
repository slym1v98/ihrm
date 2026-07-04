<?php

namespace App\Modules\Workflow\Application\QueryHandlers;

use App\Modules\Workflow\Application\Queries\ListWorkflowDelegationsQuery;
use App\Modules\Workflow\Infrastructure\Persistence\Eloquent\WorkflowDelegationModel;

final class ListWorkflowDelegationsHandler
{
    public function handle(ListWorkflowDelegationsQuery $query)
    {
        return WorkflowDelegationModel::query()
            ->when($query->delegatorId, fn ($q, $delegatorId) => $q->where('delegator_id', $delegatorId))
            ->latest()
            ->paginate(15);
    }
}
