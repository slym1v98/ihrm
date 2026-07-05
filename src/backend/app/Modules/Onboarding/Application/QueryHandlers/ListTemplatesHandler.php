<?php

namespace App\Modules\Onboarding\Application\QueryHandlers;

use App\Modules\Onboarding\Application\Queries\ListTemplatesQuery;
use App\Modules\Onboarding\Domain\Repositories\OnboardingTemplateRepositoryInterface;

class ListTemplatesHandler
{
    public function __construct(
        private readonly OnboardingTemplateRepositoryInterface $templateRepo,
    ) {}

    public function handle(ListTemplatesQuery $query): array
    {
        if ($query->departmentId || $query->positionId || $query->locationId || $query->employmentType) {
            return $this->templateRepo->findMatching(
                $query->departmentId, $query->positionId, $query->locationId, $query->employmentType,
            );
        }

        return $this->templateRepo->all();
    }
}
