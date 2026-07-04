<?php
namespace Tests\Unit\Modules\Workflow;

use App\Modules\Workflow\Application\Resolvers\DirectManagerResolver;
use PHPUnit\Framework\TestCase;

class DirectManagerResolverTest extends TestCase
{
    public function test_resolves_manager_from_context(): void
    {
        self::assertSame(['user-manager'], (new DirectManagerResolver())->resolve([], ['manager_id' => 'user-manager']));
    }

    public function test_returns_empty_without_manager(): void
    {
        self::assertSame([], (new DirectManagerResolver())->resolve([], []));
    }
}
