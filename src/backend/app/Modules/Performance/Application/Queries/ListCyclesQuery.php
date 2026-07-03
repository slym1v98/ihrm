<?php

namespace App\Modules\Performance\Application\Queries;

class ListCyclesQuery
{
    public function __construct(
        public readonly ?string $status = null,
    ) {}
}
