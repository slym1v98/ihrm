<?php

namespace App\Modules\Offboarding\Application\CommandHandlers;

use App\Modules\Offboarding\Application\Commands\CreateOffboardingPlanCommand;
use App\Modules\Offboarding\Domain\Aggregates\OffboardingPlan\OffboardingPlan;
use App\Modules\Offboarding\Domain\Aggregates\OffboardingPlan\OffboardingPlanId;
use App\Modules\Offboarding\Domain\Repositories\OffboardingPlanRepositoryInterface;

class CreateOffboardingPlanHandler
{
    public function __construct(
        private readonly OffboardingPlanRepositoryInterface $planRepo,
    ) {}

    public function handle(CreateOffboardingPlanCommand $command): OffboardingPlan
    {
        $planId = OffboardingPlanId::generate();

        $plan = OffboardingPlan::create(
            $planId, $command->employeeId, new \DateTimeImmutable($command->startDate),
        );

        $this->planRepo->save($plan);

        return $plan;
    }
}
