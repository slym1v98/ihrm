<?php

namespace App\Modules\Onboarding\Application\Commands;

class StartTaskCommand
{
    public function __construct(public readonly string $taskId) {}
}
