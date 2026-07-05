<?php

namespace App\Modules\Workflow\Application\Resolvers;

use App\Modules\Workflow\Application\Contracts\AssigneeResolver;

final class RoleResolver implements AssigneeResolver
{
    public function __construct(private readonly \Closure $lookup) {}

    public function key(): string
    {
        return 'role';
    }

    public function resolve(array $config, array $context): array
    {
        return ($this->lookup)($config['role_code']);
    }
}
