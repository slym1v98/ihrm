<?php

namespace App\Modules\Performance\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Performance\Application\Commands\CreateCycleCommand;
use App\Modules\Performance\Application\Commands\UpdateCycleCommand;
use App\Modules\Performance\Application\Commands\ActivateCycleCommand;
use App\Modules\Performance\Application\Commands\CompleteCycleCommand;
use App\Modules\Performance\Application\Commands\CancelCycleCommand;
use App\Modules\Performance\Application\CommandHandlers\CreateCycleHandler;
use App\Modules\Performance\Application\CommandHandlers\UpdateCycleHandler;
use App\Modules\Performance\Application\CommandHandlers\ActivateCycleHandler;
use App\Modules\Performance\Application\CommandHandlers\CompleteCycleHandler;
use App\Modules\Performance\Application\CommandHandlers\CancelCycleHandler;
use App\Modules\Performance\Application\Queries\ListCyclesQuery;
use App\Modules\Performance\Application\QueryHandlers\ListCyclesHandler;
use App\Modules\Performance\Domain\Aggregates\PerformanceCycle\PerformanceCycleId;
use App\Modules\Performance\Domain\Repositories\PerformanceCycleRepositoryInterface;
use App\Modules\Performance\Domain\Exceptions\PerformanceCycleNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PerformanceCycleController extends Controller
{
    public function __construct(
        private readonly CreateCycleHandler $createHandler,
        private readonly UpdateCycleHandler $updateHandler,
        private readonly ActivateCycleHandler $activateHandler,
        private readonly CompleteCycleHandler $completeHandler,
        private readonly CancelCycleHandler $cancelHandler,
        private readonly ListCyclesHandler $listHandler,
        private readonly PerformanceCycleRepositoryInterface $cycleRepo,
    ) {}

    public function index(Request $r): JsonResponse
    {
        $q = new ListCyclesQuery($r->input('status'));
        $items = $this->listHandler->handle($q);
        $data = array_map(fn($c) => [
            'id' => $c->getId()->value, 'code' => $c->getCode(), 'name' => $c->getName(),
            'description' => $c->getDescription(), 'start_date' => $c->getStartDate()->format('Y-m-d'),
            'end_date' => $c->getEndDate()->format('Y-m-d'), 'status' => $c->getStatus()->value,
            'scoring_rules' => $c->getScoringRules(), 'workflow_request_id' => $c->getWorkflowRequestId(),
        ], $items);
        return response()->json(["data" => $data]);
    }

    public function store(Request $r): JsonResponse
    {
        $cmd = new CreateCycleCommand($r->input('code'), $r->input('name'), $r->input('description'), $r->input('start_date'), $r->input('end_date'), $r->input('scoring_rules', []));
        $cycle = $this->createHandler->handle($cmd);
        return response()->json(['data' => ['id' => $cycle->getId()->value]], 201);
    }

    public function show(string $id): JsonResponse
    {
        $c = $this->cycleRepo->findById(PerformanceCycleId::fromString($id)) ?? throw new PerformanceCycleNotFoundException($id);
        return response()->json([
            'id' => $c->getId()->value, 'code' => $c->getCode(), 'name' => $c->getName(),
            'description' => $c->getDescription(), 'start_date' => $c->getStartDate()->format('Y-m-d'),
            'end_date' => $c->getEndDate()->format('Y-m-d'), 'status' => $c->getStatus()->value,
            'scoring_rules' => $c->getScoringRules(), 'workflow_request_id' => $c->getWorkflowRequestId(),
        ]);
    }

    public function update(Request $r, string $id): JsonResponse
    {
        $cmd = new UpdateCycleCommand($id, $r->input('code'), $r->input('name'), $r->input('description'), $r->input('start_date'), $r->input('end_date'), $r->input('scoring_rules', []));
        $this->updateHandler->handle($cmd);
        return response()->json(['data' => null]);
    }

    public function activate(string $id): JsonResponse
    { try { $this->activateHandler->handle(new ActivateCycleCommand($id)); return response()->json(['data' => null]); } catch (\Exception $e) { return response()->json(['data' => null, 'message' => $e->getMessage()], 422); } }

    public function complete(string $id): JsonResponse
    { try { $this->completeHandler->handle(new CompleteCycleCommand($id)); return response()->json(['data' => null]); } catch (\Exception $e) { return response()->json(['data' => null, 'message' => $e->getMessage()], 422); } }

    public function cancel(string $id): JsonResponse
    { try { $this->cancelHandler->handle(new CancelCycleCommand($id)); return response()->json(['data' => null]); } catch (\Exception $e) { return response()->json(['data' => null, 'message' => $e->getMessage()], 422); } }
}
