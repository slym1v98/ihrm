<?php

namespace App\Modules\Onboarding\Application\CommandHandlers;

use App\Modules\Onboarding\Application\Commands\ActivateOnboardingPlanCommand;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingPlan\OnboardingPlanId;
use App\Modules\Onboarding\Domain\Exceptions\OnboardingPlanNotFoundException;
use App\Modules\Onboarding\Domain\Repositories\OnboardingPlanRepositoryInterface;

class ActivateOnboardingPlanHandler
{
    public function __construct(
        private readonly OnboardingPlanRepositoryInterface $planRepo,
    ) {}

    public function handle(ActivateOnboardingPlanCommand $command): void
    {
        $plan = $this->planRepo->findById(OnboardingPlanId::fromString($command->planId));
        if (! $plan) {
            throw new OnboardingPlanNotFoundException($command->planId);
        }
        $plan->activate();
        $this->planRepo->save($plan);
        foreach ($plan->popRecordedEvents() as $event) {
            event($event);
        }
    }
}
