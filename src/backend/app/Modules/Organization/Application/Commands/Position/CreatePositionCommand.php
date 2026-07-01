<?php

namespace App\Modules\Organization\Application\Commands\Position;

use App\Modules\Organization\Domain\Aggregates\Position\PositionCode;
use App\Modules\Organization\Domain\Aggregates\Position\PositionName;

readonly class CreatePositionCommand
{
    public function __construct(
        public PositionCode $code,
        public PositionName $name,
        public ?int $level = null,
        public ?string $description = null,
    ) {}
}
