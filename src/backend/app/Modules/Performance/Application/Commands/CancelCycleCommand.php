<?php

namespace App\Modules\Performance\Application\Commands;

class CancelCycleCommand
{
    public function __construct(public readonly string $id) {}
}
