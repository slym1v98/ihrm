<?php

namespace App\Modules\Organization\Application\Commands\Branch;

use App\Modules\Organization\Domain\Aggregates\Branch\BranchId;
use App\Modules\Organization\Domain\Aggregates\Branch\BranchName;

readonly class UpdateBranchCommand
{
    public function __construct(
        public BranchId $id,
        public BranchName $name,
        public ?string $address = null,
        public ?string $phone = null,
        public ?string $email = null,
    ) {}
}
