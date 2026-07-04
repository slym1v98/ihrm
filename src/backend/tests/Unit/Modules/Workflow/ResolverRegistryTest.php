<?php
namespace Tests\Unit\Modules\Workflow;
use App\Modules\Workflow\Application\Resolvers\SpecificUserResolver;
use App\Modules\Workflow\Application\Services\ResolverRegistry;
use App\Modules\Workflow\Domain\Exceptions\WorkflowResolverNotFoundException;
use PHPUnit\Framework\TestCase;

class ResolverRegistryTest extends TestCase
{
    public function test_registry_returns_registered_resolver(): void {
        $reg = new ResolverRegistry();
        $reg->register(new SpecificUserResolver());
        self::assertInstanceOf(SpecificUserResolver::class, $reg->get('specific_user'));
    }
    public function test_unknown_resolver_throws_exception(): void {
        $this->expectException(WorkflowResolverNotFoundException::class);
        (new ResolverRegistry())->get('nope');
    }
}
