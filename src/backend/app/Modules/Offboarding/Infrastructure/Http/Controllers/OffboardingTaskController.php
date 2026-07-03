<?php

namespace App\Modules\Offboarding\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Offboarding\Application\Commands\AddOffboardingTaskCommand;
use App\Modules\Offboarding\Application\Commands\StartTaskCommand;
use App\Modules\Offboarding\Application\Commands\CompleteTaskCommand;
use App\Modules\Offboarding\Application\Commands\WaiveTaskCommand;
use App\Modules\Offboarding\Application\CommandHandlers\AddOffboardingTaskHandler;
use App\Modules\Offboarding\Application\CommandHandlers\StartTaskHandler;
use App\Modules\Offboarding\Application\CommandHandlers\CompleteTaskHandler;
use App\Modules\Offboarding\Application\CommandHandlers\WaiveTaskHandler;
use App\Modules\Offboarding\Application\Queries\ListTasksQuery;
use App\Modules\Offboarding\Application\QueryHandlers\ListTasksHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OffboardingTaskController extends Controller
{
    public function __construct(
        private readonly AddOffboardingTaskHandler $addHandler,
        private readonly StartTaskHandler $startHandler,
        private readonly CompleteTaskHandler $completeHandler,
        private readonly WaiveTaskHandler $waiveHandler,
        private readonly ListTasksHandler $listHandler,
    ) {}

    public function index(string $planId): JsonResponse
    {
        $tasks = $this->listHandler->handle(new ListTasksQuery($planId));
        return response()->json(['data' => array_map(fn($t) => ['id' => $t->getId()->value, 'title' => $t->getTitle(), 'status' => $t->getStatus()->value], $tasks)]);
    }

    public function store(Request $request, string $planId): JsonResponse
    {
        $request->validate(['title' => 'required|string|max:255', 'owner_type' => 'required|in:department,user_role', 'owner_id' => 'required|string|max:100']);
        try {
            $cmd = new AddOffboardingTaskCommand($planId, $request->input('owner_type'), $request->input('owner_id'), $request->input('title'), $request->input('description'), $request->input('due_date'), $request->boolean('requires_approval', false), false, (int) $request->input('sort_order', 0));
            $task = $this->addHandler->handle($cmd);
            return response()->json(['data' => ['id' => $task->getId()->value, 'title' => $task->getTitle()]], 201);
        } catch (\Throwable $e) { return response()->json(['message' => $e->getMessage()], 422); }
    }

    public function start(string $id): JsonResponse
    {
        try { $this->startHandler->handle(new StartTaskCommand($id)); return response()->json(['message' => 'Started']); }
        catch (\Throwable $e) { return response()->json(['message' => $e->getMessage()], 422); }
    }

    public function complete(Request $request, string $id): JsonResponse
    {
        try { $this->completeHandler->handle(new CompleteTaskCommand($id, $request->input('proof_file_object_id'))); return response()->json(['message' => 'Completed']); }
        catch (\Throwable $e) { return response()->json(['message' => $e->getMessage()], 422); }
    }

    public function waive(Request $request, string $id): JsonResponse
    {
        try { $this->waiveHandler->handle(new WaiveTaskCommand($id, $request->input('reason'))); return response()->json(['message' => 'Waived']); }
        catch (\Throwable $e) { return response()->json(['message' => $e->getMessage()], 422); }
    }
}
