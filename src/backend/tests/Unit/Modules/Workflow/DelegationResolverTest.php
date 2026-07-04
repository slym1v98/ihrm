<?php
namespace Tests\Unit\Modules\Workflow;
use App\Modules\Workflow\Application\Services\DelegationResolver;
use App\Modules\Workflow\Domain\Aggregates\WorkflowDelegation\WorkflowDelegation;
use App\Modules\Workflow\Domain\Aggregates\WorkflowDelegation\WorkflowDelegationId;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class DelegationResolverTest extends TestCase
{
    public function test_active_delegation_replaces_approver(): void {
        $r = new DelegationResolver();
        $d = [new WorkflowDelegation(WorkflowDelegationId::new(),'user-a','user-b','wa',CarbonImmutable::parse('2026-07-04 08:00'),CarbonImmutable::parse('2026-07-05 18:00'),true)];
        $result = $r->resolve(['user-a','user-c'],$d,CarbonImmutable::parse('2026-07-04 12:00'));
        self::assertSame(['user-b','user-c'],$result->effectiveApproverIds);
        self::assertSame(['user-a'=>'user-b'],$result->delegationMap);
    }
    public function test_expired_delegation_is_ignored(): void {
        $r = new DelegationResolver();
        $d = [new WorkflowDelegation(WorkflowDelegationId::new(),'user-a','user-b','wa',CarbonImmutable::parse('2026-07-01 08:00'),CarbonImmutable::parse('2026-07-02 18:00'),true)];
        $result = $r->resolve(['user-a'],$d,CarbonImmutable::parse('2026-07-04 12:00'));
        self::assertSame(['user-a'],$result->effectiveApproverIds);
    }
    public function test_inactive_delegation_is_ignored(): void {
        $r = new DelegationResolver();
        $d = [new WorkflowDelegation(WorkflowDelegationId::new(),'user-a','user-b','wa',CarbonImmutable::parse('2026-07-04 08:00'),CarbonImmutable::parse('2026-07-05 18:00'),false)];
        $result = $r->resolve(['user-a'],$d,CarbonImmutable::parse('2026-07-04 12:00'));
        self::assertSame(['user-a'],$result->effectiveApproverIds);
    }
}
