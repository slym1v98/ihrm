<?php

namespace App\Modules\Onboarding\Application\CommandHandlers;

use App\Modules\Onboarding\Application\Commands\UpdateOnboardingTemplateCommand;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingTemplate\OnboardingTemplateId;
use App\Modules\Onboarding\Domain\Exceptions\OnboardingTemplateNotFoundException;
use App\Modules\Onboarding\Domain\Repositories\OnboardingTemplateRepositoryInterface;
use App\Modules\Onboarding\Domain\ValueObjects\TemplateRules;

class UpdateOnboardingTemplateHandler
{
    public function __construct(
        private readonly OnboardingTemplateRepositoryInterface $templateRepo,
    ) {}

    public function handle(UpdateOnboardingTemplateCommand $command): void
    {
        $id = OnboardingTemplateId::fromString($command->id);
        $template = $this->templateRepo->findById($id);
        if (! $template) {
            throw new OnboardingTemplateNotFoundException($command->id);
        }
        $template->update($command->code, $command->name, TemplateRules::fromArray($command->rules));
        $this->templateRepo->save($template);
    }
}
