<?php

namespace App\Modules\Organization\Domain\Repositories;

use App\Modules\Organization\Domain\Aggregates\Position\Position;
use App\Modules\Organization\Domain\Aggregates\Position\PositionCode;
use App\Modules\Organization\Domain\Aggregates\Position\PositionId;

interface PositionRepositoryInterface
{
    public function findById(PositionId $id): Position;

    public function findByCode(PositionCode $code): ?Position;

    public function existsByCode(PositionCode $code): bool;

    public function save(Position $position): void;

    public function saveAndDispatch(Position $position): void;
}
