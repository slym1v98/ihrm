<?php

namespace App\Modules\Employee\Infrastructure\Http\Controllers;

use App\Modules\Employee\Application\CommandHandlers\Employee\ChangeEmployeeManagerHandler;
use App\Modules\Employee\Application\CommandHandlers\Employee\ChangeEmployeeStatusHandler;
use App\Modules\Employee\Application\CommandHandlers\Employee\CreateEmployeeHandler;
use App\Modules\Employee\Application\CommandHandlers\Employee\LinkEmployeeToUserHandler;
use App\Modules\Employee\Application\CommandHandlers\Employee\TransferEmployeeHandler;
use App\Modules\Employee\Application\CommandHandlers\Employee\UpdateEmployeePersonalInfoHandler;
use App\Modules\Employee\Application\Commands\Employee\ChangeEmployeeManagerCommand;
use App\Modules\Employee\Application\Commands\Employee\ChangeEmployeeStatusCommand;
use App\Modules\Employee\Application\Commands\Employee\CreateEmployeeCommand;
use App\Modules\Employee\Application\Commands\Employee\LinkEmployeeToUserCommand;
use App\Modules\Employee\Application\Commands\Employee\TransferEmployeeCommand;
use App\Modules\Employee\Application\Commands\Employee\UpdateEmployeePersonalInfoCommand;
use App\Modules\Employee\Infrastructure\Http\Requests\CreateEmployeeRequest;
use App\Modules\Employee\Infrastructure\Http\Resources\EmployeeResource;
use App\Modules\Employee\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Modules\Shared\Http\Resources\PaginatedCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController
{
    public function __construct(
        private CreateEmployeeHandler $createHandler,
        private UpdateEmployeePersonalInfoHandler $updatePersonalInfoHandler,
        private TransferEmployeeHandler $transferHandler,
        private ChangeEmployeeManagerHandler $changeManagerHandler,
        private ChangeEmployeeStatusHandler $changeStatusHandler,
        private LinkEmployeeToUserHandler $linkUserHandler,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $paginator = EmployeeModel::query()
            ->orderBy('created_at', 'desc')
            ->paginate((int) $request->input('per_page', 20), ['*'], 'page', (int) $request->input('page', 1));

        return response()->json(new PaginatedCollection($paginator->through(fn ($m) => new EmployeeResource($m))));
    }

    public function store(CreateEmployeeRequest $request): JsonResponse
    {
        $employee = $this->createHandler->handle(
            new CreateEmployeeCommand($request->input('first_name'), $request->input('last_name')),
            (string) $request->user()->id,
        );

        $model = EmployeeModel::find($employee->id()->value);
        return response()->json(['data' => new EmployeeResource($model)], 201);
    }

    public function show(string $id): JsonResponse
    {
        $model = EmployeeModel::find($id);
        abort_if(!$model, 404, 'Employee not found');
        return response()->json(['data' => new EmployeeResource($model)]);
    }

    public function updatePersonalInfo(Request $request, string $id): JsonResponse
    {
        $this->updatePersonalInfoHandler->handle(
            new UpdateEmployeePersonalInfoCommand(
                $id,
                $request->input('first_name'),
                $request->input('last_name'),
                $request->input('dob'),
                $request->input('gender'),
                $request->input('personal_email'),
                $request->input('phone'),
            ),
            (string) $request->user()->id,
        );

        return response()->json(['data' => new EmployeeResource(EmployeeModel::find($id))]);
    }

    public function transfer(Request $request, string $id): JsonResponse
    {
        $this->transferHandler->handle(
            new TransferEmployeeCommand($id, $request->input('branch_id'), $request->input('department_id'), $request->input('position_id')),
            (string) $request->user()->id,
        );

        return response()->json(['data' => new EmployeeResource(EmployeeModel::find($id))]);
    }

    public function changeManager(Request $request, string $id): JsonResponse
    {
        $this->changeManagerHandler->handle(
            new ChangeEmployeeManagerCommand($id, $request->input('manager_id')),
            (string) $request->user()->id,
        );

        return response()->json(['data' => new EmployeeResource(EmployeeModel::find($id))]);
    }

    public function changeStatus(Request $request, string $id): JsonResponse
    {
        $status = $request->input('status');
        if (!$status && $request->filled('action')) {
            $status = $request->input('action') === 'activate' ? 'active' : 'inactive';
        }

        $this->changeStatusHandler->handle(
            new ChangeEmployeeStatusCommand($id, $status, $request->input('reason')),
            (string) $request->user()->id,
        );

        return response()->json(['data' => new EmployeeResource(EmployeeModel::find($id))]);
    }

    public function linkUser(Request $request, string $id): JsonResponse
    {
        $this->linkUserHandler->handle(
            new LinkEmployeeToUserCommand($id, $request->input('user_id')),
            (string) $request->user()->id,
        );

        return response()->json(['data' => new EmployeeResource(EmployeeModel::find($id))]);
    }
}
