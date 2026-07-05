<?php

namespace Tests\Unit\Modules\Workflow;

use App\Modules\Workflow\Application\Resolvers\RoleInDepartmentResolver;
use PHPUnit\Framework\TestCase;

class RoleInDepartmentResolverTest extends TestCase
{
    public function test_resolves_users_by_role_code_and_department(): void
    {
        $resolver = new RoleInDepartmentResolver(fn (string $roleCode, string $departmentId) => [$roleCode.':'.$departmentId]);
        self::assertSame(['hr_manager:dept-1'], $resolver->resolve(['role_code' => 'hr_manager'], ['department_id' => 'dept-1']));
    }

    public function test_returns_empty_without_department(): void
    {
        $resolver = new RoleInDepartmentResolver(fn () => ['unused']);
        self::assertSame([], $resolver->resolve(['role_code' => 'hr_manager'], []));
    }
}
