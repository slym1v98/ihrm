<?php

namespace Tests\Unit\Modules\Onboarding;

use App\Modules\Onboarding\Domain\ValueObjects\OnboardingTaskStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class OnboardingTaskStatusTest extends TestCase
{
    #[Test]
    public function pending_to_in_progress_allowed(): void
    {
        $this->assertTrue(OnboardingTaskStatus::Pending->canTransitionTo(OnboardingTaskStatus::InProgress));
    }

    #[Test]
    public function pending_to_waived_allowed(): void
    {
        $this->assertTrue(OnboardingTaskStatus::Pending->canTransitionTo(OnboardingTaskStatus::Waived));
    }

    #[Test]
    public function in_progress_to_completed_allowed(): void
    {
        $this->assertTrue(OnboardingTaskStatus::InProgress->canTransitionTo(OnboardingTaskStatus::Completed));
    }

    #[Test]
    public function in_progress_to_waived_allowed(): void
    {
        $this->assertTrue(OnboardingTaskStatus::InProgress->canTransitionTo(OnboardingTaskStatus::Waived));
    }

    #[Test]
    public function terminal_transitions_blocked(): void
    {
        $this->assertFalse(OnboardingTaskStatus::Completed->canTransitionTo(OnboardingTaskStatus::Pending));
        $this->assertFalse(OnboardingTaskStatus::Completed->canTransitionTo(OnboardingTaskStatus::Waived));
        $this->assertFalse(OnboardingTaskStatus::Waived->canTransitionTo(OnboardingTaskStatus::Pending));
        $this->assertFalse(OnboardingTaskStatus::Waived->canTransitionTo(OnboardingTaskStatus::Completed));
    }

    #[Test]
    public function is_terminal_works(): void
    {
        $this->assertTrue(OnboardingTaskStatus::Completed->isTerminal());
        $this->assertTrue(OnboardingTaskStatus::Waived->isTerminal());
        $this->assertFalse(OnboardingTaskStatus::Pending->isTerminal());
        $this->assertFalse(OnboardingTaskStatus::InProgress->isTerminal());
    }
}
