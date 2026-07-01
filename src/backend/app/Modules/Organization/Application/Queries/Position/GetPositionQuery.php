<?php

namespace App\Modules\Organization\Application\Queries\Position;

use App\Modules\Organization\Domain\Aggregates\Position\PositionId;

readonly class GetPositionQuery
{
    public function __construct(public PositionId $id) {}
}
