<?php

namespace App\Modules\Offboarding\Application\Commands;

class AddOffboardingTaskCommand
{
    public function __construct(
        public readonly string $planId,
        public readonly string $ownerType,
        public readonly string $ownerId,
        public readonly string $title,
        public readonly ?string $description,
        public readonly ?string $dueDate,
        public readonly bool $requiresApproval,
        public readonly bool $isPreStart,
        public readonly int $sortOrder,
    ) {}
}
