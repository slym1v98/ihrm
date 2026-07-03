<?php

namespace App\Modules\Onboarding\Application\Commands;

class WaiveTaskCommand
{
    public function __construct(
        public readonly string $taskId,
        public readonly ?string $reason = null,
    ) {}
}
