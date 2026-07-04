<?php

namespace App\Modules\Workflow\Application\Commands;

final readonly class CreateWorkflowDelegationCommand
{
    public function __construct(
        public string $delegatorId,
        public string $delegateId,
        public ?string $roleType,
        public string $startAt,
        public string $endAt,
        public ?string $createdBy,
    ) {}
}
