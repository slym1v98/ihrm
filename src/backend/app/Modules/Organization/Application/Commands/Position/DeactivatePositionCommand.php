<?php

namespace App\Modules\Organization\Application\Commands\Position;

use App\Modules\Organization\Domain\Aggregates\Position\PositionId;

readonly class DeactivatePositionCommand
{
    public function __construct(public PositionId $id) {}
}
