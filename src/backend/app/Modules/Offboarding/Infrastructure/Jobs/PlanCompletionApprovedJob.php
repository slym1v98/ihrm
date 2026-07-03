<?php

namespace App\Modules\Offboarding\Infrastructure\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Modules\Offboarding\Domain\Repositories\OffboardingPlanRepositoryInterface;

class PlanCompletionApprovedJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(
        private readonly string $workflowRequestId,
    ) {}

    public function handle(OffboardingPlanRepositoryInterface $planRepo): void
    {
        $plan = $planRepo->findByWorkflowRequestId($this->workflowRequestId);
        if (!$plan) { return; }

        $plan->markWorkflowApproved();
        $planRepo->save($plan);

        foreach ($plan->popRecordedEvents() as $event) { event($event); }
    }
}
