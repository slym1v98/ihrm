<?php

namespace App\Modules\Employee\Application\Services;

use App\Modules\Employee\Domain\Aggregates\Contract\Contract;
use App\Modules\Employee\Domain\Aggregates\Contract\DateRange;

final class ContractRenewalPolicy
{
    /** @param Contract[] $activeContracts */
    public function hasOverlap(DateRange $term, array $activeContracts): bool
    {
        foreach ($activeContracts as $contract) {
            if ($contract->term()->overlaps($term)) {
                return true;
            }
        }
        return false;
    }
}
