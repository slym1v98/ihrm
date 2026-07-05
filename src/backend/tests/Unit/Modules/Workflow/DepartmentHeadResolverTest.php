<?php

namespace Tests\Unit\Modules\Workflow;

use App\Modules\Workflow\Application\Resolvers\DepartmentHeadResolver;
use PHPUnit\Framework\TestCase;

class DepartmentHeadResolverTest extends TestCase
{
    public function test_resolves_department_head_from_context(): void
    {
        self::assertSame(['user-head'], (new DepartmentHeadResolver)->resolve([], ['department_head_user_id' => 'user-head']));
    }

    public function test_returns_empty_without_department_head(): void
    {
        self::assertSame([], (new DepartmentHeadResolver)->resolve([], []));
    }
}
