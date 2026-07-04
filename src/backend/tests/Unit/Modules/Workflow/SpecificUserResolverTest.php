<?php
namespace Tests\Unit\Modules\Workflow;
use App\Modules\Workflow\Application\Resolvers\SpecificUserResolver;
use PHPUnit\Framework\TestCase;

class SpecificUserResolverTest extends TestCase
{
    public function test_resolve_returns_configured_user_id(): void {
        self::assertSame(['user-1'], (new SpecificUserResolver())->resolve(['user_id'=>'user-1'], []));
    }
}
