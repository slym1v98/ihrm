<?php

namespace App\Modules\Workflow\Application\Resolvers;

use App\Modules\Workflow\Application\Contracts\AssigneeResolver;

final class DepartmentHeadResolver implements AssigneeResolver
{
    public function key(): string
    {
        return 'department_head';
    }

    public function resolve(array $config, array $context): array
    {
        return isset($context['department_head_user_id']) ? [$context['department_head_user_id']] : [];
    }
}
