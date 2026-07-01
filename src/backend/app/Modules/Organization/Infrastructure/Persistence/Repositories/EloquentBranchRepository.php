<?php

namespace App\Modules\Organization\Infrastructure\Persistence\Repositories;

use App\Modules\Organization\Domain\Aggregates\Branch\Branch;
use App\Modules\Organization\Domain\Aggregates\Branch\BranchCode;
use App\Modules\Organization\Domain\Aggregates\Branch\BranchId;
use App\Modules\Organization\Domain\Aggregates\Branch\BranchName;
use App\Modules\Organization\Domain\Aggregates\Branch\BranchStatus;
use App\Modules\Organization\Domain\Exceptions\BranchNotFoundException;
use App\Modules\Organization\Domain\Repositories\BranchRepositoryInterface;
use App\Modules\Organization\Infrastructure\Persistence\Eloquent\BranchModel;
use Illuminate\Support\Facades\Event;

class EloquentBranchRepository implements BranchRepositoryInterface
{
    public function __construct(private BranchModel $model) {}

    public function findById(BranchId $id): Branch
    {
        $record = $this->model->find($id->value);
        if (!$record) throw new BranchNotFoundException($id->value);
        return $this->toDomain($record);
    }

    public function findByCode(BranchCode $code): ?Branch
    {
        $record = $this->model->where('code', $code->value)->first();
        return $record ? $this->toDomain($record) : null;
    }

    public function existsByCode(BranchCode $code): bool
    {
        return $this->model->where('code', $code->value)->exists();
    }

    public function hasActiveDepartments(BranchId $id): bool
    {
        $record = $this->model->find($id->value);
        return $record && $record->activeDepartments()->exists();
    }

    public function save(Branch $branch): void
    {
        $this->model->updateOrCreate(
            ['id' => $branch->id()->value],
            [
                'code' => $branch->code()->value,
                'name' => $branch->name()->value,
                'address' => $branch->address(),
                'phone' => $branch->phone(),
                'email' => $branch->email(),
                'status' => $branch->status()->value,
            ]
        );
    }

    public function saveAndDispatch(Branch $branch): void
    {
        $this->save($branch);
        foreach ($branch->releaseEvents() as $event) {
            Event::dispatch($event);
        }
    }

    private function toDomain(BranchModel $record): Branch
    {
        return Branch::reconstitute(
            BranchId::fromString($record->id),
            BranchCode::fromString($record->code),
            BranchName::fromString($record->name),
            $record->address,
            $record->phone,
            $record->email,
            BranchStatus::from($record->status),
        );
    }
}
