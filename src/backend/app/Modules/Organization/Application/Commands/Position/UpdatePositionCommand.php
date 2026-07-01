<?php

namespace App\Modules\Organization\Application\Commands\Position;

use App\Modules\Organization\Domain\Aggregates\Position\PositionId;
use App\Modules\Organization\Domain\Aggregates\Position\PositionName;

readonly class UpdatePositionCommand
{
    public function __construct(
        public PositionId $id,
        public PositionName $name,
        public ?int $level = null,
        public ?string $description = null,
    ) {}
}
