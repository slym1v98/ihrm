<?php

namespace App\Modules\Offboarding\Application\Commands;

class StartTaskCommand
{
    public function __construct(public readonly string $taskId) {}
}
