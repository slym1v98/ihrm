<?php

namespace App\Modules\Onboarding\Application\CommandHandlers;

use App\Modules\Onboarding\Application\Commands\CreateOnboardingPlanCommand;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingPlan\OnboardingPlan;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingPlan\OnboardingPlanId;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingTemplate\OnboardingTemplateId;
use App\Modules\Onboarding\Domain\Repositories\OnboardingPlanRepositoryInterface;
use App\Modules\Onboarding\Domain\Repositories\OnboardingTemplateRepositoryInterface;
use App\Modules\Onboarding\Domain\Exceptions\OnboardingTemplateNotFoundException;

class CreateOnboardingPlanHandler
{
    public function __construct(
        private readonly OnboardingPlanRepositoryInterface $planRepo,
        private readonly OnboardingTemplateRepositoryInterface $templateRepo,
    ) {}

    public function handle(CreateOnboardingPlanCommand $command): OnboardingPlan
    {
        $planId = OnboardingPlanId::generate();

        if ($command->templateId) {
            $templateId = OnboardingTemplateId::fromString($command->templateId);
            $template = $this->templateRepo->findById($templateId);
            if (!$template) {
                throw new OnboardingTemplateNotFoundException($command->templateId);
            }
            $plan = $template->generatePlan(
                $planId, $command->employeeId, $command->candidateId,
                new \DateTimeImmutable($command->startDate),
            );
        } else {
            $plan = OnboardingPlan::create(
                $planId, $command->employeeId, $command->candidateId, null,
                new \DateTimeImmutable($command->startDate),
            );
        }

        $this->planRepo->save($plan);
        return $plan;
    }
}
