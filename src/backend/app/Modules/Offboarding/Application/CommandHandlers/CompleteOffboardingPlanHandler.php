<?php

namespace App\Modules\Offboarding\Application\CommandHandlers;

use App\Modules\Offboarding\Application\Commands\CompleteOffboardingPlanCommand;
use App\Modules\Offboarding\Domain\Aggregates\OffboardingPlan\OffboardingPlanId;
use App\Modules\Offboarding\Domain\Repositories\OffboardingPlanRepositoryInterface;
use App\Modules\Offboarding\Domain\Exceptions\OffboardingPlanNotFoundException;
use App\Modules\Offboarding\Infrastructure\Services\PlanWorkflowService;

class CompleteOffboardingPlanHandler
{
    public function __construct(
        private readonly OffboardingPlanRepositoryInterface $planRepo,
        private readonly PlanWorkflowService $workflowService,
    ) {}

    public function handle(CompleteOffboardingPlanCommand $command): void
    {
        $plan = $this->planRepo->findById(OffboardingPlanId::fromString($command->planId));
        if (!$plan) { throw new OffboardingPlanNotFoundException($command->planId); }

        if ($command->workflowTemplateId) {
            $requestId = $this->workflowService->startWorkflow(
                $command->workflowTemplateId, 'offboarding_plan', $command->planId,
            );
            $plan->setWorkflowRequestId($requestId);
        }

        $plan->complete();
        $this->planRepo->save($plan);
        foreach ($plan->popRecordedEvents() as $event) { event($event); }
    }
}
