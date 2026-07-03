<?php

namespace Tests\Unit\Modules\Onboarding;

use App\Modules\Onboarding\Domain\Aggregates\OnboardingTask\OnboardingTask;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingTask\OnboardingTaskId;
use App\Modules\Onboarding\Domain\Exceptions\InvalidStatusTransitionException;
use App\Modules\Onboarding\Domain\ValueObjects\OnboardingTaskStatus;
use App\Modules\Onboarding\Domain\ValueObjects\OwnerType;
use App\Modules\Onboarding\Domain\ValueObjects\TaskType;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class OnboardingTaskTest extends TestCase
{
    private function createTask(): OnboardingTask
    {
        return OnboardingTask::create(
            OnboardingTaskId::generate(), 'plan-1',
            TaskType::SystemDefined, OwnerType::UserRole, 'hr',
            'Setup laptop', null, null, false, false, 1,
        );
    }

    #[Test]
    public function create_sets_pending(): void
    {
        $task = $this->createTask();
        $this->assertEquals(OnboardingTaskStatus::Pending, $task->getStatus());
    }

    #[Test]
    public function start_transitions_to_in_progress(): void
    {
        $task = $this->createTask();
        $task->start();
        $this->assertEquals(OnboardingTaskStatus::InProgress, $task->getStatus());
    }

    #[Test]
    public function complete_transitions_to_completed(): void
    {
        $task = $this->createTask();
        $task->start();
        $task->complete();
        $this->assertEquals(OnboardingTaskStatus::Completed, $task->getStatus());
    }

    #[Test]
    public function waive_transitions_to_waived(): void
    {
        $task = $this->createTask();
        $task->waive('No longer needed');
        $this->assertEquals(OnboardingTaskStatus::Waived, $task->getStatus());
    }

    #[Test]
    public function terminal_reverts_blocked(): void
    {
        $task = $this->createTask();
        $task->start();
        $task->complete();

        $this->expectException(InvalidStatusTransitionException::class);
        $task->start();
    }

    #[Test]
    public function requires_approval_blocks_completion(): void
    {
        $task = OnboardingTask::create(
            OnboardingTaskId::generate(), 'plan-1',
            TaskType::SystemDefined, OwnerType::UserRole, 'it',
            'Approve laptop', null, null, true, false, 1,
        );
        $task->start();
        $task->complete('proof-123');

        // Should stay in_progress because requires_approval
        $this->assertEquals(OnboardingTaskStatus::InProgress, $task->getStatus());

        $task->markApproved();
        $this->assertEquals(OnboardingTaskStatus::Completed, $task->getStatus());
    }

    #[Test]
    public function update_blocked_after_completion(): void
    {
        $task = $this->createTask();
        $task->start();
        $task->complete();

        $this->expectException(\RuntimeException::class);
        $task->update('New title', null);
    }
}
