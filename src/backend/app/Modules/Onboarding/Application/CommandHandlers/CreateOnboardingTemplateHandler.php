<?php

namespace App\Modules\Onboarding\Application\CommandHandlers;

use App\Modules\Onboarding\Application\Commands\CreateOnboardingTemplateCommand;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingTemplate\OnboardingTemplate;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingTemplate\OnboardingTemplateId;
use App\Modules\Onboarding\Domain\Repositories\OnboardingTemplateRepositoryInterface;
use App\Modules\Onboarding\Domain\ValueObjects\TemplateRules;

class CreateOnboardingTemplateHandler
{
    public function __construct(
        private readonly OnboardingTemplateRepositoryInterface $templateRepo,
    ) {}

    public function handle(CreateOnboardingTemplateCommand $command): OnboardingTemplate
    {
        $rules = TemplateRules::fromArray($command->rules);
        $template = OnboardingTemplate::create(
            OnboardingTemplateId::generate(),
            $command->code, $command->name, $rules,
        );
        $this->templateRepo->save($template);

        return $template;
    }
}
