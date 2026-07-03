<?php

namespace App\Modules\Performance\Domain\Events;

class CompetencyTemplateCreated
{
    public function __construct(
        public readonly string $templateId,
    ) {}
}
