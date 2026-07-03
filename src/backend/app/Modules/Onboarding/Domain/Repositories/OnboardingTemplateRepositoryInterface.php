<?php

namespace App\Modules\Onboarding\Domain\Repositories;

use App\Modules\Onboarding\Domain\Aggregates\OnboardingTemplate\OnboardingTemplate;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingTemplate\OnboardingTemplateId;

interface OnboardingTemplateRepositoryInterface
{
    public function findById(OnboardingTemplateId $id): ?OnboardingTemplate;
    public function findByCode(string $code): ?OnboardingTemplate;
    /** @return OnboardingTemplate[] */
    public function findMatching(?string $departmentId, ?string $positionId, ?string $locationId, ?string $employmentType): array;
    /** @return OnboardingTemplate[] */
    public function all(): array;
    public function save(OnboardingTemplate $template): void;
    public function delete(OnboardingTemplateId $id): void;
}
