<?php

namespace App\Modules\Organization\Application\Commands\Branch;

use App\Modules\Organization\Domain\Aggregates\Branch\BranchId;

readonly class ActivateBranchCommand
{
    public function __construct(public BranchId $id) {}
}
