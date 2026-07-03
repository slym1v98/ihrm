<?php

namespace App\Modules\Offboarding\Application\CommandHandlers;

use App\Modules\Offboarding\Application\Commands\CancelOffboardingPlanCommand;
use App\Modules\Offboarding\Domain\Aggregates\OffboardingPlan\OffboardingPlanId;
use App\Modules\Offboarding\Domain\Repositories\OffboardingPlanRepositoryInterface;
use App\Modules\Offboarding\Domain\Exceptions\OffboardingPlanNotFoundException;

class CancelOffboardingPlanHandler
{
    public function __construct(
        private readonly OffboardingPlanRepositoryInterface $planRepo,
    ) {}

    public function handle(CancelOffboardingPlanCommand $command): void
    {
        $plan = $this->planRepo->findById(OffboardingPlanId::fromString($command->planId));
        if (!$plan) { throw new OffboardingPlanNotFoundException($command->planId); }
        $plan->cancel();
        $this->planRepo->save($plan);
        foreach ($plan->popRecordedEvents() as $event) { event($event); }
    }
}
