<?php

namespace App\Modules\Workflow\Infrastructure\Http\Controllers;

use App\Modules\Workflow\Application\CommandHandlers\CreateWorkflowDelegationHandler;
use App\Modules\Workflow\Application\CommandHandlers\RevokeWorkflowDelegationHandler;
use App\Modules\Workflow\Application\Commands\CreateWorkflowDelegationCommand;
use App\Modules\Workflow\Application\Commands\RevokeWorkflowDelegationCommand;
use App\Modules\Workflow\Application\Queries\ListWorkflowDelegationsQuery;
use App\Modules\Workflow\Application\QueryHandlers\ListWorkflowDelegationsHandler;
use App\Modules\Workflow\Infrastructure\Http\Requests\CreateWorkflowDelegationRequest;
use App\Modules\Workflow\Infrastructure\Http\Resources\WorkflowDelegationResource;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class WorkflowDelegationController extends Controller
{
    public function index(Request $request, ListWorkflowDelegationsHandler $handler)
    {
        return WorkflowDelegationResource::collection($handler->handle(new ListWorkflowDelegationsQuery($request->query('delegator_id'))));
    }

    public function store(CreateWorkflowDelegationRequest $request, CreateWorkflowDelegationHandler $handler)
    {
        $delegation = $handler->handle(new CreateWorkflowDelegationCommand(
            $request->input('delegator_id'),
            $request->input('delegate_id'),
            $request->input('role_type'),
            $request->input('start_at'),
            $request->input('end_at'),
            $request->user()?->getAuthIdentifier(),
        ));
        return (new WorkflowDelegationResource($delegation))->response()->setStatusCode(201);
    }

    public function destroy(string $id, RevokeWorkflowDelegationHandler $handler)
    {
        $handler->handle(new RevokeWorkflowDelegationCommand($id));
        return response()->noContent();
    }
}
