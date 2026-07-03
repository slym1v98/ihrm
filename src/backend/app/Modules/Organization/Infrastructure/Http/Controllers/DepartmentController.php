<?php

namespace App\Modules\Organization\Infrastructure\Http\Controllers;

use App\Modules\Organization\Application\CommandHandlers\Department\ActivateDepartmentHandler;
use App\Modules\Organization\Application\CommandHandlers\Department\CreateDepartmentHandler;
use App\Modules\Organization\Application\CommandHandlers\Department\DeactivateDepartmentHandler;
use App\Modules\Organization\Application\CommandHandlers\Department\MoveDepartmentHandler;
use App\Modules\Organization\Application\CommandHandlers\Department\UpdateDepartmentHandler;
use App\Modules\Organization\Application\Commands\Department\ActivateDepartmentCommand;
use App\Modules\Organization\Application\Commands\Department\CreateDepartmentCommand;
use App\Modules\Organization\Application\Commands\Department\DeactivateDepartmentCommand;
use App\Modules\Organization\Application\Commands\Department\MoveDepartmentCommand;
use App\Modules\Organization\Application\Commands\Department\UpdateDepartmentCommand;
use App\Modules\Organization\Domain\Aggregates\Branch\BranchId;
use App\Modules\Organization\Domain\Aggregates\Department\DepartmentCode;
use App\Modules\Organization\Domain\Aggregates\Department\DepartmentId;
use App\Modules\Organization\Domain\Aggregates\Department\DepartmentName;
use App\Modules\Organization\Infrastructure\Http\Requests\CreateDepartmentRequest;
use App\Modules\Organization\Infrastructure\Http\Requests\MoveDepartmentRequest;
use App\Modules\Organization\Infrastructure\Http\Requests\UpdateDepartmentRequest;
use App\Modules\Organization\Infrastructure\Http\Resources\DepartmentResource;
use App\Modules\Organization\Infrastructure\Persistence\Eloquent\DepartmentModel;
use App\Modules\Shared\Http\Resources\PaginatedCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartmentController
{
    public function __construct(
        private CreateDepartmentHandler $createHandler,
        private UpdateDepartmentHandler $updateHandler,
        private MoveDepartmentHandler $moveHandler,
        private ActivateDepartmentHandler $activateHandler,
        private DeactivateDepartmentHandler $deactivateHandler,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $paginator = DepartmentModel::query()
            ->with('parent')
            ->when($request->input('branch_id'), fn ($q) => $q->where('branch_id', $request->input('branch_id')))
            ->when($request->input('parent_id'), fn ($q) => $q->where('parent_id', $request->input('parent_id')))
            ->when($request->input('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->orderBy('name')
            ->paginate((int) $request->input('per_page', 20), ['*'], 'page', (int) $request->input('page', 1));

        return response()->json(new PaginatedCollection($paginator->through(fn ($m) => new DepartmentResource($m))));
    }

    public function store(CreateDepartmentRequest $request): JsonResponse
    {
        $department = $this->createHandler->handle(
            new CreateDepartmentCommand(
                BranchId::fromString($request->input('branch_id')),
                DepartmentCode::fromString($request->input('code')),
                DepartmentName::fromString($request->input('name')),
                $request->has('parent_id') ? DepartmentId::fromString($request->input('parent_id')) : null,
            ),
            (string) $request->user()->id,
        );

        $model = DepartmentModel::with('parent')->find($department->id()->value);
        return response()->json(['data' => new DepartmentResource($model)], 201);
    }

    public function show(string $id): JsonResponse
    {
        $model = DepartmentModel::with('parent')->find($id);
        abort_if(!$model, 404, 'Department not found');
        return response()->json(['data' => new DepartmentResource($model)]);
    }

    public function update(UpdateDepartmentRequest $request, string $id): JsonResponse
    {
        $this->updateHandler->handle(
            new UpdateDepartmentCommand(
                DepartmentId::fromString($id),
                DepartmentName::fromString($request->input('name')),
                $request->input('manager_employee_id'),
            ),
            (string) $request->user()->id,
        );

        $model = DepartmentModel::with('parent')->find($id);
        return response()->json(['data' => new DepartmentResource($model)]);
    }

    public function move(MoveDepartmentRequest $request, string $id): JsonResponse
    {
        $this->moveHandler->handle(
            new MoveDepartmentCommand(
                DepartmentId::fromString($id),
                $request->filled('new_parent_id') ? DepartmentId::fromString($request->input('new_parent_id')) : null,
            ),
            (string) $request->user()->id,
        );

        $model = DepartmentModel::with('parent')->find($id);
        return response()->json(['data' => new DepartmentResource($model)]);
    }

    public function activate(Request $request, string $id): JsonResponse
    {
        $this->activateHandler->handle(
            new ActivateDepartmentCommand(DepartmentId::fromString($id)),
            (string) $request->user()->id,
        );
        return response()->json(['message' => 'Activated']);
    }

    public function deactivate(Request $request, string $id): JsonResponse
    {
        $this->deactivateHandler->handle(
            new DeactivateDepartmentCommand(DepartmentId::fromString($id)),
            (string) $request->user()->id,
        );
        return response()->json(['message' => 'Deactivated']);
    }
}
