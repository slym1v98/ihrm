<?php

namespace App\Modules\Performance\Infrastructure\Persistence\Repositories;

use App\Modules\Performance\Domain\Aggregates\PerformanceCycle\PerformanceCycle;
use App\Modules\Performance\Domain\Aggregates\PerformanceCycle\PerformanceCycleId;
use App\Modules\Performance\Domain\Repositories\PerformanceCycleRepositoryInterface;
use App\Modules\Performance\Domain\ValueObjects\CycleStatus;
use App\Modules\Performance\Infrastructure\Persistence\Eloquent\PerformanceCycleModel;

class EloquentPerformanceCycleRepository implements PerformanceCycleRepositoryInterface
{
    public function findById(PerformanceCycleId $id): ?PerformanceCycle { $m = PerformanceCycleModel::find($id->value); return $m ? $this->toDomain($m) : null; }
    public function findByCode(string $code): ?PerformanceCycle { $m = PerformanceCycleModel::where('code', $code)->first(); return $m ? $this->toDomain($m) : null; }
    public function all(): array { return PerformanceCycleModel::orderByDesc('created_at')->get()->map(fn($m) => $this->toDomain($m))->toArray(); }
    public function save(PerformanceCycle $cycle): void
    {
        PerformanceCycleModel::updateOrCreate(['id' => $cycle->getId()->value], [
            'code' => $cycle->getCode(), 'name' => $cycle->getName(), 'description' => $cycle->getDescription(),
            'start_date' => $cycle->getStartDate()->format('Y-m-d'), 'end_date' => $cycle->getEndDate()->format('Y-m-d'),
            'status' => $cycle->getStatus()->value, 'scoring_rules' => $cycle->getScoringRules(), 'workflow_request_id' => $cycle->getWorkflowRequestId(),
        ]);
    }
    private function toDomain(PerformanceCycleModel $m): PerformanceCycle
    {
        return PerformanceCycle::reconstitute(PerformanceCycleId::fromString($m->id), $m->code, $m->name, $m->description, new \DateTimeImmutable($m->start_date), new \DateTimeImmutable($m->end_date), CycleStatus::from($m->status), $m->scoring_rules ?? [], $m->workflow_request_id);
    }
}
