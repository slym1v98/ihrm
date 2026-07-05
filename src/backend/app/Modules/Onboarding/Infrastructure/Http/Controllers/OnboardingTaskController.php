<?php

namespace App\Modules\Onboarding\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Onboarding\Application\CommandHandlers\AddOnboardingTaskHandler;
use App\Modules\Onboarding\Application\CommandHandlers\CompleteTaskHandler;
use App\Modules\Onboarding\Application\CommandHandlers\RemoveOnboardingTaskHandler;
use App\Modules\Onboarding\Application\CommandHandlers\StartTaskHandler;
use App\Modules\Onboarding\Application\CommandHandlers\WaiveTaskHandler;
use App\Modules\Onboarding\Application\Commands\AddOnboardingTaskCommand;
use App\Modules\Onboarding\Application\Commands\CompleteTaskCommand;
use App\Modules\Onboarding\Application\Commands\StartTaskCommand;
use App\Modules\Onboarding\Application\Commands\WaiveTaskCommand;
use App\Modules\Onboarding\Application\Queries\ListTasksQuery;
use App\Modules\Onboarding\Application\QueryHandlers\ListTasksHandler;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingTask\OnboardingTaskId;
use App\Modules\Onboarding\Domain\Exceptions\OnboardingTaskNotFoundException;
use App\Modules\Onboarding\Domain\Repositories\OnboardingTaskRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnboardingTaskController extends Controller
{
    public function __construct(
        private readonly AddOnboardingTaskHandler $addHandler,
        private readonly RemoveOnboardingTaskHandler $removeHandler,
        private readonly StartTaskHandler $startHandler,
        private readonly CompleteTaskHandler $completeHandler,
        private readonly WaiveTaskHandler $waiveHandler,
        private readonly ListTasksHandler $listHandler,
        private readonly OnboardingTaskRepositoryInterface $taskRepo,
    ) {}

    public function index(string $planId): JsonResponse
    {
        $query = new ListTasksQuery($planId);
        $tasks = $this->listHandler->handle($query);

        return response()->json(['data' => array_map(fn ($t) => [
            'id' => $t->getId()->value,
            'title' => $t->getTitle(),
            'status' => $t->getStatus()->value,
            'task_type' => $t->getTaskType()->value,
            'owner_type' => $t->getOwnerType()->value,
            'owner_id' => $t->getOwnerId(),
            'due_date' => $t->getDueDate()?->format('Y-m-d'),
            'requires_approval' => $t->isRequiresApproval(),
            'is_pre_start' => $t->isPreStart(),
            'sort_order' => $t->getSortOrder(),
        ], $tasks)]);
    }

    public function store(Request $request, string $planId): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'owner_type' => 'required|string|in:department,user_role',
            'owner_id' => 'required|string|max:100',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'requires_approval' => 'boolean',
            'is_pre_start' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $command = new AddOnboardingTaskCommand(
            planId: $planId,
            ownerType: $request->input('owner_type'),
            ownerId: $request->input('owner_id'),
            title: $request->input('title'),
            description: $request->input('description'),
            dueDate: $request->input('due_date'),
            requiresApproval: $request->boolean('requires_approval', false),
            isPreStart: $request->boolean('is_pre_start', false),
            sortOrder: (int) $request->input('sort_order', 0),
        );
        try {
            $task = $this->addHandler->handle($command);
        } catch (\Throwable $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json(['data' => [
            'id' => $task->getId()->value,
            'title' => $task->getTitle(),
            'status' => $task->getStatus()->value,
        ]], 201);
    }

    public function show(string $id): JsonResponse
    {
        $task = $this->taskRepo->findById(OnboardingTaskId::fromString($id));
        if (! $task) {
            throw new OnboardingTaskNotFoundException($id);
        }

        return response()->json(['data' => [
            'id' => $task->getId()->value,
            'title' => $task->getTitle(),
            'status' => $task->getStatus()->value,
            'owner_type' => $task->getOwnerType()->value,
            'owner_id' => $task->getOwnerId(),
        ]]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $request->validate(['title' => 'required|string|max:255']);
        $task = $this->taskRepo->findById(OnboardingTaskId::fromString($id));
        if (! $task) {
            throw new OnboardingTaskNotFoundException($id);
        }
        $task->update($request->input('title'), $request->input('description'));
        $this->taskRepo->save($task);

        return response()->json(['message' => 'Updated']);
    }

    public function start(string $id): JsonResponse
    {
        try {
            $this->startHandler->handle(new StartTaskCommand($id));
        } catch (\Throwable $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json(['message' => 'Task started']);
    }

    public function complete(Request $request, string $id): JsonResponse
    {
        $command = new CompleteTaskCommand(
            taskId: $id,
            proofFileObjectId: $request->input('proof_file_object_id'),
            workflowTemplateId: $request->input('workflow_template_id'),
        );
        try {
            $this->completeHandler->handle($command);
        } catch (\Throwable $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json(['message' => 'Task completed']);
    }

    public function waive(Request $request, string $id): JsonResponse
    {
        $command = new WaiveTaskCommand(
            taskId: $id,
            reason: $request->input('reason'),
        );
        try {
            $this->waiveHandler->handle($command);
        } catch (\Throwable $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json(['message' => 'Task waived']);
    }
}
