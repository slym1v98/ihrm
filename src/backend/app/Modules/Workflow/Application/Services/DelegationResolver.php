<?php
namespace App\Modules\Workflow\Application\Services;
use App\Modules\Workflow\Domain\Aggregates\WorkflowDelegation\WorkflowDelegation;
use Carbon\CarbonImmutable;
final class DelegationResolver
{
    public function resolve(array $approverIds, array $delegations, CarbonImmutable $at): DelegationResult
    {
        $effective = []; $map = [];
        foreach ($approverIds as $id) {
            $active = null;
            foreach ($delegations as $d) {
                if ($d->delegatorId() === $id && $d->isEffectiveAt($at)) { $active = $d; break; }
            }
            if ($active !== null && $active->delegateId() !== $id) {
                $effective[] = $active->delegateId(); $map[$id] = $active->delegateId();
            } else { $effective[] = $id; }
        }
        return new DelegationResult($effective, $map);
    }
}
