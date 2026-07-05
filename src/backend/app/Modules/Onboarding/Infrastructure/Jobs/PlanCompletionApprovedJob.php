<?php

namespace App\Modules\Onboarding\Infrastructure\Jobs;

use App\Modules\Onboarding\Domain\Repositories\OnboardingPlanRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class PlanCompletionApprovedJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(
        private readonly string $workflowRequestId,
    ) {}

    public function handle(OnboardingPlanRepositoryInterface $planRepo): void
    {
        $plan = $planRepo->findByWorkflowRequestId($this->workflowRequestId);
        if (! $plan) {
            return;
        }

        $plan->markWorkflowApproved();
        $planRepo->save($plan);

        foreach ($plan->popRecordedEvents() as $event) {
            event($event);
        }
    }
}
