<?php

namespace App\Modules\Workflow\Application\Resolvers;

use App\Modules\Workflow\Application\Contracts\AssigneeResolver;

final class SpecificUserResolver implements AssigneeResolver
{
    public function key(): string
    {
        return 'specific_user';
    }

    public function resolve(array $config, array $context): array
    {
        return [$config['user_id']];
    }
}
