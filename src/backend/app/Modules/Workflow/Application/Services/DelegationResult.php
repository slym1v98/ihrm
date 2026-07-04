<?php
namespace App\Modules\Workflow\Application\Services;
final class DelegationResult
{
    public function __construct(
        public array $effectiveApproverIds,
        public array $delegationMap,
    ) {}
}
