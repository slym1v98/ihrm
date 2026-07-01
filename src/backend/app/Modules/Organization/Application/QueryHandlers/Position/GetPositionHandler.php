<?php

namespace App\Modules\Organization\Application\QueryHandlers\Position;

use App\Modules\Organization\Application\Queries\Position\GetPositionQuery;
use App\Modules\Organization\Domain\Aggregates\Position\Position;
use App\Modules\Organization\Domain\Repositories\PositionRepositoryInterface;

class GetPositionHandler
{
    public function __construct(private PositionRepositoryInterface $positionRepository) {}

    public function handle(GetPositionQuery $query): Position
    {
        return $this->positionRepository->findById($query->id);
    }
}
