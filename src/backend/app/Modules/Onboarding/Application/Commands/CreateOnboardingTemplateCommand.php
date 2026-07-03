<?php

namespace App\Modules\Onboarding\Application\Commands;

class CreateOnboardingTemplateCommand
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly array $rules,
    ) {}
}
