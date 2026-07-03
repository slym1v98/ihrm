<?php

namespace App\Modules\Offboarding\Application\CommandHandlers;

use App\Modules\Offboarding\Application\Commands\CreateOffboardingPlanCommand;
use App\Modules\Offboarding\Domain\Aggregates\OffboardingPlan\OffboardingPlan;
use App\Modules\Offboarding\Domain\Aggregates\OffboardingPlan\OffboardingPlanId;
use App\Modules\Offboarding\Domain\Aggregates\Offboarding\OffboardingId;
use App\Modules\Offboarding\Domain\Repositories\OffboardingPlanRepositoryInterface;
use App\Modules\Offboarding\Domain\Repositories\OffboardingRepositoryInterface;
use App\Modules\Offboarding\Domain\Exceptions\OffboardingNotFoundException;

class CreateOffboardingPlanHandler
{
    public function __construct(
        private readonly OffboardingPlanRepositoryInterface $planRepo,
        private readonly OffboardingRepositoryInterface $templateRepo,
    ) {}

    public function handle(CreateOffboardingPlanCommand $command): OffboardingPlan
    {
        $planId = OffboardingPlanId::generate();

        if (null) {
            $templateId = OffboardingId::fromString(null);
            $template = $this->templateRepo->findById($templateId);
            if (!$template) {
                throw new OffboardingNotFoundException(null);
            }
            $plan = $template->generatePlan(
                $planId, $command->employeeId, $command->candidateId,
                new \DateTimeImmutable($command->startDate),
            );
        } else {
            $plan = OffboardingPlan::create(
                $planId, $command->employeeId, $command->candidateId, null,
                new \DateTimeImmutable($command->startDate),
            );
        }

        $this->planRepo->save($plan);
        return $plan;
    }
}
