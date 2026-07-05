<?php

namespace App\Modules\Organization\Infrastructure\Http\Controllers;

use App\Modules\Organization\Application\CommandHandlers\Position\ActivatePositionHandler;
use App\Modules\Organization\Application\CommandHandlers\Position\CreatePositionHandler;
use App\Modules\Organization\Application\CommandHandlers\Position\DeactivatePositionHandler;
use App\Modules\Organization\Application\CommandHandlers\Position\UpdatePositionHandler;
use App\Modules\Organization\Application\Commands\Position\ActivatePositionCommand;
use App\Modules\Organization\Application\Commands\Position\CreatePositionCommand;
use App\Modules\Organization\Application\Commands\Position\DeactivatePositionCommand;
use App\Modules\Organization\Application\Commands\Position\UpdatePositionCommand;
use App\Modules\Organization\Domain\Aggregates\Position\PositionCode;
use App\Modules\Organization\Domain\Aggregates\Position\PositionId;
use App\Modules\Organization\Domain\Aggregates\Position\PositionName;
use App\Modules\Organization\Infrastructure\Http\Requests\CreatePositionRequest;
use App\Modules\Organization\Infrastructure\Http\Requests\UpdatePositionRequest;
use App\Modules\Organization\Infrastructure\Http\Resources\PositionResource;
use App\Modules\Organization\Infrastructure\Persistence\Eloquent\PositionModel;
use App\Modules\Shared\Http\Resources\PaginatedCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PositionController
{
    public function __construct(
        private CreatePositionHandler $createHandler,
        private UpdatePositionHandler $updateHandler,
        private ActivatePositionHandler $activateHandler,
        private DeactivatePositionHandler $deactivateHandler,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $paginator = PositionModel::query()
            ->orderBy('name')
            ->paginate((int) $request->input('per_page', 20), ['*'], 'page', (int) $request->input('page', 1));

        return response()->json(new PaginatedCollection($paginator->through(fn ($m) => new PositionResource($m))));
    }

    public function store(CreatePositionRequest $request): JsonResponse
    {
        $position = $this->createHandler->handle(
            new CreatePositionCommand(
                PositionCode::fromString($request->input('code')),
                PositionName::fromString($request->input('name')),
                $request->has('level') && $request->input('level') !== null ? (int) $request->input('level') : null,
                $request->input('description'),
            ),
            (string) $request->user()->id,
        );

        $model = PositionModel::find($position->id()->value);

        return response()->json(['data' => new PositionResource($model)], 201);
    }

    public function show(string $id): JsonResponse
    {
        $model = PositionModel::find($id);
        abort_if(! $model, 404, 'Position not found');

        return response()->json(['data' => new PositionResource($model)]);
    }

    public function update(UpdatePositionRequest $request, string $id): JsonResponse
    {
        $this->updateHandler->handle(
            new UpdatePositionCommand(
                PositionId::fromString($id),
                PositionName::fromString($request->input('name')),
                $request->has('level') && $request->input('level') !== null ? (int) $request->input('level') : null,
                $request->input('description'),
            ),
            (string) $request->user()->id,
        );

        $model = PositionModel::find($id);

        return response()->json(['data' => new PositionResource($model)]);
    }

    public function activate(Request $request, string $id): JsonResponse
    {
        $this->activateHandler->handle(
            new ActivatePositionCommand(PositionId::fromString($id)),
            (string) $request->user()->id,
        );

        return response()->json(['message' => 'Activated']);
    }

    public function deactivate(Request $request, string $id): JsonResponse
    {
        $this->deactivateHandler->handle(
            new DeactivatePositionCommand(PositionId::fromString($id)),
            (string) $request->user()->id,
        );

        return response()->json(['message' => 'Deactivated']);
    }
}
