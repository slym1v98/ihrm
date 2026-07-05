<?php

namespace App\Modules\Workflow\Application\Contracts;

interface AssigneeResolver
{
    public function key(): string;

    public function resolve(array $config, array $context): array;
}
