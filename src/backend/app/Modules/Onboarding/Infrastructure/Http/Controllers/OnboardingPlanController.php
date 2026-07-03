<?php

namespace App\Modules\Onboarding\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Onboarding\Application\Commands\CreateOnboardingPlanCommand;
use App\Modules\Onboarding\Application\Commands\ActivateOnboardingPlanCommand;
use App\Modules\Onboarding\Application\Commands\CancelOnboardingPlanCommand;
use App\Modules\Onboarding\Application\Commands\CompleteOnboardingPlanCommand;
use App\Modules\Onboarding\Application\CommandHandlers\CreateOnboardingPlanHandler;
use App\Modules\Onboarding\Application\CommandHandlers\ActivateOnboardingPlanHandler;
use App\Modules\Onboarding\Application\CommandHandlers\CancelOnboardingPlanHandler;
use App\Modules\Onboarding\Application\CommandHandlers\CompleteOnboardingPlanHandler;
use App\Modules\Onboarding\Application\Queries\ListPlansQuery;
use App\Modules\Onboarding\Application\QueryHandlers\ListPlansHandler;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingPlan\OnboardingPlanId;
use App\Modules\Onboarding\Domain\Repositories\OnboardingPlanRepositoryInterface;
use App\Modules\Onboarding\Domain\Exceptions\OnboardingPlanNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnboardingPlanController extends Controller
{
    public function __construct(
        private readonly CreateOnboardingPlanHandler $createHandler,
        private readonly ActivateOnboardingPlanHandler $activateHandler,
        private readonly CancelOnboardingPlanHandler $cancelHandler,
        private readonly CompleteOnboardingPlanHandler $completeHandler,
        private readonly ListPlansHandler $listHandler,
        private readonly OnboardingPlanRepositoryInterface $planRepo,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = new ListPlansQuery($request->input('employee_id'));
        return response()->json(['data' => $this->listHandler->handle($query)]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'employee_id' => 'required|uuid',
            'candidate_id' => 'nullable|uuid',
            'template_id' => 'nullable|uuid',
            'start_date' => 'required|date',
        ]);

        $command = new CreateOnboardingPlanCommand(
            employeeId: $request->input('employee_id'),
            candidateId: $request->input('candidate_id'),
            templateId: $request->input('template_id'),
            startDate: $request->input('start_date'),
        );
        $plan = $this->createHandler->handle($command);
        return response()->json(['data' => [
            'id' => $plan->getId()->value,
            'status' => $plan->getStatus()->value,
        ]], 201);
    }

    public function show(string $id): JsonResponse
    {
        $plan = $this->planRepo->findById(OnboardingPlanId::fromString($id));
        if (!$plan) { throw new OnboardingPlanNotFoundException($id); }
        return response()->json(['data' => [
            'id' => $plan->getId()->value,
            'employee_id' => $plan->getEmployeeId(),
            'status' => $plan->getStatus()->value,
            'start_date' => $plan->getStartDate()->format('Y-m-d'),
            'tasks' => array_map(fn($t) => [
                'id' => $t->getId()->value,
                'title' => $t->getTitle(),
                'status' => $t->getStatus()->value,
                'owner_type' => $t->getOwnerType()->value,
                'owner_id' => $t->getOwnerId(),
            ], $plan->getTasks()),
        ]]);
    }

    public function activate(string $id): JsonResponse
    {
        $this->activateHandler->handle(new ActivateOnboardingPlanCommand($id));
        return response()->json(['message' => 'Plan activated']);
    }

    public function cancel(string $id): JsonResponse
    {
        $this->cancelHandler->handle(new CancelOnboardingPlanCommand($id));
        return response()->json(['message' => 'Plan cancelled']);
    }

    public function complete(Request $request, string $id): JsonResponse
    {
        $command = new CompleteOnboardingPlanCommand(
            planId: $id,
            workflowTemplateId: $request->input('workflow_template_id'),
        );
        $this->completeHandler->handle($command);
        return response()->json(['message' => 'Plan completed']);
    }
}
