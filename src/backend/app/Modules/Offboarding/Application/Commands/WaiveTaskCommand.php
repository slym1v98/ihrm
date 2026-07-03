<?php

namespace App\Modules\Offboarding\Application\Commands;

class WaiveTaskCommand
{
    public function __construct(
        public readonly string $taskId,
        public readonly ?string $reason = null,
    ) {}
}
