<?php
namespace App\Modules\Workflow\Application\Resolvers;
use App\Modules\Workflow\Application\Contracts\AssigneeResolver;
final class DirectManagerResolver implements AssigneeResolver
{
    public function key(): string { return 'direct_manager'; }
    public function resolve(array $config, array $context): array { return isset($context['manager_id']) ? [$context['manager_id']] : []; }
}
