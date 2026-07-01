<?php

namespace App\Modules\Organization\Infrastructure\Persistence\Repositories;

use App\Modules\Organization\Domain\Aggregates\Position\Position;
use App\Modules\Organization\Domain\Aggregates\Position\PositionCode;
use App\Modules\Organization\Domain\Aggregates\Position\PositionId;
use App\Modules\Organization\Domain\Aggregates\Position\PositionName;
use App\Modules\Organization\Domain\Aggregates\Position\PositionStatus;
use App\Modules\Organization\Domain\Exceptions\PositionNotFoundException;
use App\Modules\Organization\Domain\Repositories\PositionRepositoryInterface;
use App\Modules\Organization\Infrastructure\Persistence\Eloquent\PositionModel;
use Illuminate\Support\Facades\Event;

class EloquentPositionRepository implements PositionRepositoryInterface
{
    public function __construct(private PositionModel $model) {}

    public function findById(PositionId $id): Position
    {
        $record = $this->model->find($id->value);
        if (!$record) throw new PositionNotFoundException($id->value);
        return $this->toDomain($record);
    }

    public function findByCode(PositionCode $code): ?Position
    {
        $record = $this->model->where('code', $code->value)->first();
        return $record ? $this->toDomain($record) : null;
    }

    public function existsByCode(PositionCode $code): bool
    {
        return $this->model->where('code', $code->value)->exists();
    }

    public function save(Position $position): void
    {
        $this->model->updateOrCreate(
            ['id' => $position->id()->value],
            [
                'code' => $position->code()->value,
                'name' => $position->name()->value,
                'level' => $position->level(),
                'description' => $position->description(),
                'status' => $position->status()->value,
            ]
        );
    }

    public function saveAndDispatch(Position $position): void
    {
        $this->save($position);
        foreach ($position->releaseEvents() as $event) {
            Event::dispatch($event);
        }
    }

    private function toDomain(PositionModel $record): Position
    {
        return Position::reconstitute(
            PositionId::fromString($record->id),
            PositionCode::fromString($record->code),
            PositionName::fromString($record->name),
            $record->level,
            $record->description,
            PositionStatus::from($record->status),
        );
    }
}
