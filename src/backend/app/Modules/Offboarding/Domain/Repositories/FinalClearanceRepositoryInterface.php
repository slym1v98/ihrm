<?php

namespace App\Modules\Offboarding\Domain\Repositories;

use App\Modules\Offboarding\Domain\Aggregates\FinalClearance\FinalClearance;
use App\Modules\Offboarding\Domain\Aggregates\FinalClearance\FinalClearanceId;

interface FinalClearanceRepositoryInterface
{
    public function findById(FinalClearanceId $id): ?FinalClearance;
    public function findByPlanId(string $planId): ?FinalClearance;
    public function save(FinalClearance $clearance): void;
}
