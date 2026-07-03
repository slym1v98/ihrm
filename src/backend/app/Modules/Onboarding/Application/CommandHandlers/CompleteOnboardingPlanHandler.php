<?php

namespace App\Modules\Onboarding\Application\CommandHandlers;

use App\Modules\Onboarding\Application\Commands\CompleteOnboardingPlanCommand;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingPlan\OnboardingPlanId;
use App\Modules\Onboarding\Domain\Repositories\OnboardingPlanRepositoryInterface;
use App\Modules\Onboarding\Domain\Exceptions\OnboardingPlanNotFoundException;
use App\Modules\Onboarding\Infrastructure\Services\PlanWorkflowService;

class CompleteOnboardingPlanHandler
{
    public function __construct(
        private readonly OnboardingPlanRepositoryInterface $planRepo,
        private readonly PlanWorkflowService $workflowService,
    ) {}

    public function handle(CompleteOnboardingPlanCommand $command): void
    {
        $plan = $this->planRepo->findById(OnboardingPlanId::fromString($command->planId));
        if (!$plan) { throw new OnboardingPlanNotFoundException($command->planId); }

        if ($command->workflowTemplateId) {
            $requestId = $this->workflowService->startWorkflow(
                $command->workflowTemplateId, 'onboarding_plan', $command->planId,
            );
            $plan->setWorkflowRequestId($requestId);
        }

        $plan->complete();
        $this->planRepo->save($plan);
        foreach ($plan->popRecordedEvents() as $event) { event($event); }
    }
}
