<?php

namespace App\Modules\Organization\Infrastructure\Http\Controllers;

use App\Modules\Organization\Application\CommandHandlers\Branch\ActivateBranchHandler;
use App\Modules\Organization\Application\CommandHandlers\Branch\CreateBranchHandler;
use App\Modules\Organization\Application\CommandHandlers\Branch\DeactivateBranchHandler;
use App\Modules\Organization\Application\CommandHandlers\Branch\UpdateBranchHandler;
use App\Modules\Organization\Application\Commands\Branch\ActivateBranchCommand;
use App\Modules\Organization\Application\Commands\Branch\CreateBranchCommand;
use App\Modules\Organization\Application\Commands\Branch\DeactivateBranchCommand;
use App\Modules\Organization\Application\Commands\Branch\UpdateBranchCommand;
use App\Modules\Organization\Domain\Aggregates\Branch\BranchCode;
use App\Modules\Organization\Domain\Aggregates\Branch\BranchId;
use App\Modules\Organization\Domain\Aggregates\Branch\BranchName;
use App\Modules\Organization\Infrastructure\Http\Requests\CreateBranchRequest;
use App\Modules\Organization\Infrastructure\Http\Requests\UpdateBranchRequest;
use App\Modules\Organization\Infrastructure\Http\Resources\BranchResource;
use App\Modules\Organization\Infrastructure\Persistence\Eloquent\BranchModel;
use App\Modules\Shared\Http\Resources\PaginatedCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BranchController
{
    public function __construct(
        private CreateBranchHandler $createHandler,
        private UpdateBranchHandler $updateHandler,
        private ActivateBranchHandler $activateHandler,
        private DeactivateBranchHandler $deactivateHandler,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $paginator = BranchModel::query()
            ->orderBy('name')
            ->paginate((int) $request->input('per_page', 20), ['*'], 'page', (int) $request->input('page', 1));

        return response()->json(new PaginatedCollection($paginator->through(fn ($m) => new BranchResource($m))));
    }

    public function store(CreateBranchRequest $request): JsonResponse
    {
        $branch = $this->createHandler->handle(
            new CreateBranchCommand(
                BranchCode::fromString($request->input('code')),
                BranchName::fromString($request->input('name')),
                $request->input('address'),
                $request->input('phone'),
                $request->input('email'),
            ),
            (string) $request->user()->id,
        );

        $model = BranchModel::find($branch->id()->value);
        return response()->json(['data' => new BranchResource($model)], 201);
    }

    public function show(string $id): JsonResponse
    {
        $model = BranchModel::find($id);
        abort_if(!$model, 404, 'Branch not found');
        return response()->json(['data' => new BranchResource($model)]);
    }

    public function update(UpdateBranchRequest $request, string $id): JsonResponse
    {
        $this->updateHandler->handle(
            new UpdateBranchCommand(
                BranchId::fromString($id),
                BranchName::fromString($request->input('name')),
                $request->input('address'),
                $request->input('phone'),
                $request->input('email'),
            ),
            (string) $request->user()->id,
        );

        $model = BranchModel::find($id);
        return response()->json(['data' => new BranchResource($model)]);
    }

    public function activate(Request $request, string $id): JsonResponse
    {
        $this->activateHandler->handle(
            new ActivateBranchCommand(BranchId::fromString($id)),
            (string) $request->user()->id,
        );
        return response()->json(['message' => 'Activated']);
    }

    public function deactivate(Request $request, string $id): JsonResponse
    {
        $this->deactivateHandler->handle(
            new DeactivateBranchCommand(BranchId::fromString($id)),
            (string) $request->user()->id,
        );
        return response()->json(['message' => 'Deactivated']);
    }
}
