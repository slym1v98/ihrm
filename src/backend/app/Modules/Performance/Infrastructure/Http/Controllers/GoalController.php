<?php

namespace App\Modules\Performance\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Performance\Application\Commands\CreateGoalCommand;
use App\Modules\Performance\Application\Commands\UpdateGoalCommand;
use App\Modules\Performance\Application\Commands\CompleteGoalCommand;
use App\Modules\Performance\Application\CommandHandlers\CreateGoalHandler;
use App\Modules\Performance\Application\CommandHandlers\UpdateGoalHandler;
use App\Modules\Performance\Application\CommandHandlers\CompleteGoalHandler;
use App\Modules\Performance\Application\Queries\ListGoalsQuery;
use App\Modules\Performance\Application\QueryHandlers\ListGoalsHandler;
use App\Modules\Performance\Domain\Aggregates\Goal\GoalId;
use App\Modules\Performance\Domain\Repositories\GoalRepositoryInterface;
use App\Modules\Performance\Domain\Exceptions\GoalNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GoalController extends Controller
{
    public function __construct(
        private readonly CreateGoalHandler $createHandler,
        private readonly UpdateGoalHandler $updateHandler,
        private readonly CompleteGoalHandler $completeHandler,
        private readonly ListGoalsHandler $listHandler,
        private readonly GoalRepositoryInterface $goalRepo,
    ) {}

    public function index(Request $r): JsonResponse
    {
        $q = new ListGoalsQuery($r->input('cycle_id'), $r->input('employee_id'));
        $items = $this->listHandler->handle($q);
        $data = array_map(fn($g) => [
            'id' => $g->getId()->value, 'cycle_id' => $g->getCycleId(), 'employee_id' => $g->getEmployeeId(),
            'title' => $g->getTitle(), 'description' => $g->getDescription(), 'weight' => $g->getWeight(),
            'target_value' => $g->getTargetValue(), 'actual_value' => $g->getActualValue(),
            'status' => $g->getStatus()->value, 'sort_order' => $g->getSortOrder(),
        ], $items);
        return response()->json($data);
    }

    public function store(Request $r): JsonResponse
    {
        $cmd = new CreateGoalCommand($r->input('cycle_id'), $r->input('employee_id'), $r->input('title'), $r->input('description'), (float) $r->input('weight', 1.0), $r->input('target_value'), (int) $r->input('sort_order', 0));
        $g = $this->createHandler->handle($cmd);
        return response()->json(['id' => $g->getId()->value], 201);
    }

    public function show(string $id): JsonResponse
    {
        $g = $this->goalRepo->findById(GoalId::fromString($id)) ?? throw new GoalNotFoundException($id);
        return response()->json([
            'id' => $g->getId()->value, 'cycle_id' => $g->getCycleId(), 'employee_id' => $g->getEmployeeId(),
            'title' => $g->getTitle(), 'description' => $g->getDescription(), 'weight' => $g->getWeight(),
            'target_value' => $g->getTargetValue(), 'actual_value' => $g->getActualValue(),
            'status' => $g->getStatus()->value, 'sort_order' => $g->getSortOrder(),
        ]);
    }

    public function update(Request $r, string $id): JsonResponse
    { try { $this->updateHandler->handle(new UpdateGoalCommand($id, $r->input('title'), $r->input('description'), (float) $r->input('weight', 1.0), $r->input('target_value'))); return response()->json(['message' => 'Updated']); } catch (\Exception $e) { return response()->json(['error' => $e->getMessage()], 422); } }

    public function complete(Request $r, string $id): JsonResponse
    { try { $this->completeHandler->handle(new CompleteGoalCommand($id, $r->input('actual_value'))); return response()->json(['message' => 'Completed']); } catch (\Exception $e) { return response()->json(['error' => $e->getMessage()], 422); } }
}
