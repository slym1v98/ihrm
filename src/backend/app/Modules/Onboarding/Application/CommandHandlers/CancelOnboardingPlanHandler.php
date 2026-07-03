<?php

namespace App\Modules\Onboarding\Application\CommandHandlers;

use App\Modules\Onboarding\Application\Commands\CancelOnboardingPlanCommand;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingPlan\OnboardingPlanId;
use App\Modules\Onboarding\Domain\Repositories\OnboardingPlanRepositoryInterface;
use App\Modules\Onboarding\Domain\Exceptions\OnboardingPlanNotFoundException;

class CancelOnboardingPlanHandler
{
    public function __construct(
        private readonly OnboardingPlanRepositoryInterface $planRepo,
    ) {}

    public function handle(CancelOnboardingPlanCommand $command): void
    {
        $plan = $this->planRepo->findById(OnboardingPlanId::fromString($command->planId));
        if (!$plan) { throw new OnboardingPlanNotFoundException($command->planId); }
        $plan->cancel();
        $this->planRepo->save($plan);
        foreach ($plan->popRecordedEvents() as $event) { event($event); }
    }
}
