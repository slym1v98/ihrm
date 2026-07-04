<?php

namespace App\Modules\Workflow\Application\Queries;

final readonly class ListWorkflowDelegationsQuery
{
    public function __construct(public ?string $delegatorId = null) {}
}
