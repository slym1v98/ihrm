<?php

namespace Tests\Unit\Modules\Onboarding;

use App\Modules\Onboarding\Domain\Aggregates\OnboardingPlan\OnboardingPlan;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingPlan\OnboardingPlanId;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingTask\OnboardingTask;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingTask\OnboardingTaskId;
use App\Modules\Onboarding\Domain\Exceptions\InvalidStatusTransitionException;
use App\Modules\Onboarding\Domain\Exceptions\MandatoryTaskIncompleteException;
use App\Modules\Onboarding\Domain\ValueObjects\OnboardingPlanStatus;
use App\Modules\Onboarding\Domain\ValueObjects\OwnerType;
use App\Modules\Onboarding\Domain\ValueObjects\TaskType;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class OnboardingPlanTest extends TestCase
{
    private function createPlan(): OnboardingPlan
    {
        return OnboardingPlan::create(
            OnboardingPlanId::generate(),
            'emp-1', null, null,
            new \DateTimeImmutable('2026-07-15'),
        );
    }

    private function createCompletedTask(string $planId): OnboardingTask
    {
        $task = OnboardingTask::create(
            OnboardingTaskId::generate(), $planId,
            TaskType::SystemDefined, OwnerType::UserRole, 'hr',
            'Test task', null, null, false, false, 1,
        );
        $task->start();
        $task->complete();

        return $task;
    }

    #[Test]
    public function create_sets_draft_status(): void
    {
        $plan = $this->createPlan();
        $this->assertEquals(OnboardingPlanStatus::Draft, $plan->getStatus());
    }

    #[Test]
    public function activate_transitions_to_active(): void
    {
        $plan = $this->createPlan();
        $plan->addGeneratedTask($this->createCompletedTask($plan->getId()->value));
        $plan->activate();
        $this->assertEquals(OnboardingPlanStatus::Active, $plan->getStatus());
    }

    #[Test]
    public function activate_fails_without_tasks(): void
    {
        $this->expectException(\RuntimeException::class);
        $plan = $this->createPlan();
        $plan->activate();
    }

    #[Test]
    public function cancel_transitions_to_cancelled(): void
    {
        $plan = $this->createPlan();
        $plan->cancel();
        $this->assertEquals(OnboardingPlanStatus::Cancelled, $plan->getStatus());
    }

    #[Test]
    public function completed_transition_blocked(): void
    {
        $this->expectException(InvalidStatusTransitionException::class);
        $plan = $this->createPlan();
        $plan->cancel();
        $plan->activate();
    }

    #[Test]
    public function complete_fails_with_pending_tasks(): void
    {
        $this->expectException(MandatoryTaskIncompleteException::class);
        $plan = $this->createPlan();
        $plan->addGeneratedTask(
            OnboardingTask::create(
                OnboardingTaskId::generate(), $plan->getId()->value,
                TaskType::SystemDefined, OwnerType::UserRole, 'hr',
                'Pending task', null, null, false, false, 1,
            )
        );
        $plan->activate();
        $plan->complete();
    }

    #[Test]
    public function complete_succeeds_when_all_tasks_done(): void
    {
        $plan = $this->createPlan();
        $task = $this->createCompletedTask($plan->getId()->value);
        $plan->addGeneratedTask($task);
        $plan->activate();
        $plan->complete();
        $this->assertEquals(OnboardingPlanStatus::Completed, $plan->getStatus());
        $this->assertNotNull($plan->getCompletedAt());
    }

    #[Test]
    public function mark_workflow_approved_completes_plan(): void
    {
        $plan = $this->createPlan();
        $plan->addGeneratedTask($this->createCompletedTask($plan->getId()->value));
        $plan->activate();
        $plan->setWorkflowRequestId('wf-1');
        $plan->complete(); // stays active because workflow_request_id set
        $this->assertEquals(OnboardingPlanStatus::Active, $plan->getStatus());

        $plan->markWorkflowApproved();
        $this->assertEquals(OnboardingPlanStatus::Completed, $plan->getStatus());
    }

    #[Test]
    public function records_events(): void
    {
        $plan = $this->createPlan();
        $events = $plan->popRecordedEvents();
        $this->assertCount(1, $events); // OnboardingPlanCreated
        $this->assertStringContainsString('OnboardingPlanCreated', $events[0]::class);
    }
}
