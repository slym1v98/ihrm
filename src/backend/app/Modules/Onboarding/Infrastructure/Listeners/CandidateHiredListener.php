<?php

namespace App\Modules\Onboarding\Infrastructure\Listeners;

use App\Modules\Onboarding\Application\Commands\CreateOnboardingPlanCommand;
use App\Modules\Onboarding\Application\CommandHandlers\CreateOnboardingPlanHandler;
use App\Modules\Onboarding\Domain\Repositories\OnboardingTemplateRepositoryInterface;

class CandidateHiredListener
{
    public function __construct(
        private readonly OnboardingTemplateRepositoryInterface $templateRepo,
        private readonly CreateOnboardingPlanHandler $createPlanHandler,
    ) {}

    public function handle($event): void
    {
        $templates = $this->templateRepo->findMatching(
            $event->departmentId ?? null,
            $event->positionId ?? null,
            $event->locationId ?? null,
            $event->employmentType ?? null,
        );

        $templateId = !empty($templates) ? $templates[0]->getId()->value : null;

        $command = new CreateOnboardingPlanCommand(
            employeeId: $event->employeeId,
            candidateId: $event->candidateId ?? null,
            templateId: $templateId,
            startDate: $event->startDate ?? date('Y-m-d'),
        );

        $this->createPlanHandler->handle($command);
    }
}
