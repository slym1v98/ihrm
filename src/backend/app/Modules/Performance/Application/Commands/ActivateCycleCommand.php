<?php

namespace App\Modules\Performance\Application\Commands;

class ActivateCycleCommand
{
    public function __construct(public readonly string $id) {}
}
