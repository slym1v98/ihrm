<?php

namespace App\Modules\Offboarding\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Offboarding\Application\Commands\CreateOffboardingPlanCommand;
use App\Modules\Offboarding\Application\Commands\ActivateOffboardingPlanCommand;
use App\Modules\Offboarding\Application\Commands\CompleteOffboardingPlanCommand;
use App\Modules\Offboarding\Application\Commands\CompleteFinalClearanceCommand;
use App\Modules\Offboarding\Application\CommandHandlers\CreateOffboardingPlanHandler;
use App\Modules\Offboarding\Application\CommandHandlers\ActivateOffboardingPlanHandler;
use App\Modules\Offboarding\Application\CommandHandlers\CompleteOffboardingPlanHandler;
use App\Modules\Offboarding\Application\CommandHandlers\CompleteFinalClearanceHandler;
use App\Modules\Offboarding\Application\Queries\ListPlansQuery;
use App\Modules\Offboarding\Application\QueryHandlers\ListPlansHandler;
use App\Modules\Offboarding\Domain\Aggregates\OffboardingPlan\OffboardingPlanId;
use App\Modules\Offboarding\Domain\Repositories\OffboardingPlanRepositoryInterface;
use App\Modules\Offboarding\Domain\Exceptions\OffboardingPlanNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OffboardingPlanController extends Controller
{
    public function __construct(
        private readonly CreateOffboardingPlanHandler $createHandler,
        private readonly ActivateOffboardingPlanHandler $activateHandler,
        private readonly CompleteOffboardingPlanHandler $completeHandler,
        private readonly CompleteFinalClearanceHandler $clearanceHandler,
        private readonly ListPlansHandler $listHandler,
        private readonly OffboardingPlanRepositoryInterface $planRepo,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $items = $this->listHandler->handle(new ListPlansQuery());
        return response()->json(['data' => array_map(fn($p) => ['id' => $p->getId()->value, 'status' => $p->getStatus()->value], $items)]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate(['offboarding_request_id' => 'required|uuid', 'start_date' => 'nullable|date']);
        $cmd = new CreateOffboardingPlanCommand($request->input('offboarding_request_id'), $request->input('employee_id', ''), $request->input('start_date', date('Y-m-d')));
        $plan = $this->createHandler->handle($cmd);
        return response()->json(['data' => ['id' => $plan->getId()->value, 'status' => $plan->getStatus()->value]], 201);
    }

    public function show(string $id): JsonResponse
    {
        $plan = $this->planRepo->findById(OffboardingPlanId::fromString($id));
        if (!$plan) { throw new OffboardingPlanNotFoundException($id); }
        return response()->json(['data' => ['id' => $plan->getId()->value, 'status' => $plan->getStatus()->value]]);
    }

    public function activate(string $id): JsonResponse
    {
        try { $this->activateHandler->handle(new ActivateOffboardingPlanCommand($id)); return response()->json(['message' => 'Activated']); }
        catch (\Throwable $e) { return response()->json(['message' => $e->getMessage()], 422); }
    }

    public function complete(Request $request, string $id): JsonResponse
    {
        try { $this->completeHandler->handle(new CompleteOffboardingPlanCommand($id)); return response()->json(['message' => 'Completed']); }
        catch (\Throwable $e) { return response()->json(['message' => $e->getMessage()], 422); }
    }

    public function finalClearance(Request $request, string $id): JsonResponse
    {
        try { $this->clearanceHandler->handle(new CompleteFinalClearanceCommand($id, $request->user()?->id ?? $request->input('cleared_by', 'system'), $request->input('payroll_notes'))); return response()->json(['message' => 'Cleared']); }
        catch (\Throwable $e) { return response()->json(['message' => $e->getMessage()], 422); }
    }
}
