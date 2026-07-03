<?php

namespace App\Modules\Offboarding\Application\CommandHandlers;

use App\Modules\Offboarding\Application\Commands\ActivateOffboardingPlanCommand;
use App\Modules\Offboarding\Domain\Aggregates\OffboardingPlan\OffboardingPlanId;
use App\Modules\Offboarding\Domain\Repositories\OffboardingPlanRepositoryInterface;
use App\Modules\Offboarding\Domain\Exceptions\OffboardingPlanNotFoundException;

class ActivateOffboardingPlanHandler
{
    public function __construct(
        private readonly OffboardingPlanRepositoryInterface $planRepo,
    ) {}

    public function handle(ActivateOffboardingPlanCommand $command): void
    {
        $plan = $this->planRepo->findById(OffboardingPlanId::fromString($command->planId));
        if (!$plan) { throw new OffboardingPlanNotFoundException($command->planId); }
        $plan->activate();
        $this->planRepo->save($plan);
        foreach ($plan->popRecordedEvents() as $event) { event($event); }
    }
}
