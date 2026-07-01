<?php

namespace App\Modules\Employee\Infrastructure\Http\Controllers;

use App\Modules\Employee\Application\CommandHandlers\Contract\ActivateContractHandler;
use App\Modules\Employee\Application\CommandHandlers\Contract\CreateContractHandler;
use App\Modules\Employee\Application\CommandHandlers\Contract\RenewContractHandler;
use App\Modules\Employee\Application\CommandHandlers\Contract\TerminateContractHandler;
use App\Modules\Employee\Application\Commands\Contract\ActivateContractCommand;
use App\Modules\Employee\Application\Commands\Contract\CreateContractCommand;
use App\Modules\Employee\Application\Commands\Contract\RenewContractCommand;
use App\Modules\Employee\Application\Commands\Contract\TerminateContractCommand;
use App\Modules\Employee\Infrastructure\Http\Resources\ContractResource;
use App\Modules\Employee\Infrastructure\Persistence\Eloquent\ContractModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContractController
{
    public function __construct(
        private CreateContractHandler $createHandler,
        private ActivateContractHandler $activateHandler,
        private RenewContractHandler $renewHandler,
        private TerminateContractHandler $terminateHandler,
    ) {}

    public function index(Request $request, string $employeeId): JsonResponse
    {
        $paginator = ContractModel::where('employee_id', $employeeId)
            ->orderBy('created_at', 'desc')
            ->paginate((int) $request->input('per_page', 20), ['*'], 'page', (int) $request->input('page', 1));

        return response()->json(new \App\Modules\Shared\Http\Resources\PaginatedCollection(
            $paginator->through(fn ($m) => new ContractResource($m))
        ));
    }

    public function store(Request $request, string $employeeId): JsonResponse
    {
        $contract = $this->createHandler->handle(
            new CreateContractCommand($employeeId, $request->input('contract_type'), $request->input('start_date'), $request->input('end_date'), $request->input('base_salary')),
            (string) $request->user()->id,
        );

        $model = ContractModel::find($contract->id()->value);
        return response()->json(['data' => new ContractResource($model)], 201);
    }

    public function activate(Request $request, string $id): JsonResponse
    {
        $this->activateHandler->handle(
            new ActivateContractCommand($id),
            (string) $request->user()->id,
        );

        return response()->json(['data' => new ContractResource(ContractModel::find($id))]);
    }

    public function renew(Request $request, string $id): JsonResponse
    {
        $this->renewHandler->handle(
            new RenewContractCommand($id, $request->input('start_date'), $request->input('end_date'), $request->input('base_salary')),
            (string) $request->user()->id,
        );

        return response()->json(['data' => new ContractResource(ContractModel::find($id))]);
    }

    public function terminate(Request $request, string $id): JsonResponse
    {
        $this->terminateHandler->handle(
            new TerminateContractCommand($id),
            (string) $request->user()->id,
        );

        return response()->json(['data' => new ContractResource(ContractModel::find($id))]);
    }
}
