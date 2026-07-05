<?php

namespace Tests\Unit\Modules\Workflow;

use App\Modules\Workflow\Application\Resolvers\RoleResolver;
use PHPUnit\Framework\TestCase;

class RoleResolverTest extends TestCase
{
    public function test_resolves_users_by_role_code(): void
    {
        $resolver = new RoleResolver(fn (string $roleCode) => $roleCode === 'hr_manager' ? ['u-1', 'u-2'] : []);
        self::assertSame(['u-1', 'u-2'], $resolver->resolve(['role_code' => 'hr_manager'], []));
    }
}
