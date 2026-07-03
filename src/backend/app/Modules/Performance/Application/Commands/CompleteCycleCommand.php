<?php

namespace App\Modules\Performance\Application\Commands;

class CompleteCycleCommand
{
    public function __construct(public readonly string $id) {}
}
