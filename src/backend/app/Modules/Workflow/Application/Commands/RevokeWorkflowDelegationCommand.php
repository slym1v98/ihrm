<?php

namespace App\Modules\Workflow\Application\Commands;

final readonly class RevokeWorkflowDelegationCommand
{
    public function __construct(public string $id) {}
}
