<?php

namespace App\Modules\Offboarding\Infrastructure\Persistence\Repositories;

use App\Modules\Offboarding\Domain\Aggregates\FinalClearance\FinalClearance;
use App\Modules\Offboarding\Domain\Aggregates\FinalClearance\FinalClearanceId;
use App\Modules\Offboarding\Domain\Repositories\FinalClearanceRepositoryInterface;
use App\Modules\Offboarding\Infrastructure\Persistence\Eloquent\FinalClearanceModel;

class EloquentFinalClearanceRepository implements FinalClearanceRepositoryInterface
{
    public function findById(FinalClearanceId $id): ?FinalClearance
    {
        $model = FinalClearanceModel::find($id->value);
        return $model ? $this->toDomain($model) : null;
    }

    public function findByPlanId(string $planId): ?FinalClearance
    {
        $model = FinalClearanceModel::where('offboarding_plan_id', $planId)->first();
        return $model ? $this->toDomain($model) : null;
    }

    public function save(FinalClearance $clearance): void
    {
        FinalClearanceModel::updateOrCreate(
            ['id' => $clearance->getId()->value],
            [
                'offboarding_plan_id' => $clearance->getPlanId(),
                'employee_id' => $clearance->getEmployeeId(),
                'cleared_at' => $clearance->getClearedAt()->format('Y-m-d H:i:s'),
                'cleared_by' => $clearance->getClearedBy(),
                'asset_obligations_met' => $clearance->isAssetObligationsMet(),
                'payroll_notes' => $clearance->getPayrollNotes(),
            ]
        );
    }

    private function toDomain(FinalClearanceModel $model): FinalClearance
    {
        return FinalClearance::reconstitute(
            FinalClearanceId::fromString($model->id),
            $model->offboarding_plan_id,
            $model->employee_id,
            new \DateTimeImmutable($model->cleared_at),
            $model->cleared_by,
            $model->asset_obligations_met,
            $model->payroll_notes,
        );
    }
}
