<?php

namespace App\Modules\Organization\Application\Commands\Branch;

use App\Modules\Organization\Domain\Aggregates\Branch\BranchCode;
use App\Modules\Organization\Domain\Aggregates\Branch\BranchName;

readonly class CreateBranchCommand
{
    public function __construct(
        public BranchCode $code,
        public BranchName $name,
        public ?string $address = null,
        public ?string $phone = null,
        public ?string $email = null,
    ) {}
}
