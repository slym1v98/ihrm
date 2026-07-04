<?php
namespace App\Modules\Workflow\Application\Resolvers;
use App\Modules\Workflow\Application\Contracts\AssigneeResolver;
final class RoleInDepartmentResolver implements AssigneeResolver
{
    public function __construct(private readonly \Closure $lookup) {}
    public function key(): string { return 'role_in_department'; }
    public function resolve(array $config, array $context): array {
        $dept = $context['department_id'] ?? null;
        return $dept ? ($this->lookup)($config['role_code'], $dept) : [];
    }
}
