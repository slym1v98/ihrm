<?php

namespace Tests\Unit\Modules\Onboarding;

use App\Modules\Onboarding\Domain\ValueObjects\OnboardingPlanStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class OnboardingPlanStatusTest extends TestCase
{
    #[Test]
    public function draft_to_active_allowed(): void
    {
        $this->assertTrue(OnboardingPlanStatus::Draft->canTransitionTo(OnboardingPlanStatus::Active));
    }

    #[Test]
    public function draft_to_cancelled_allowed(): void
    {
        $this->assertTrue(OnboardingPlanStatus::Draft->canTransitionTo(OnboardingPlanStatus::Cancelled));
    }

    #[Test]
    public function draft_to_completed_blocked(): void
    {
        $this->assertFalse(OnboardingPlanStatus::Draft->canTransitionTo(OnboardingPlanStatus::Completed));
    }

    #[Test]
    public function active_to_completed_allowed(): void
    {
        $this->assertTrue(OnboardingPlanStatus::Active->canTransitionTo(OnboardingPlanStatus::Completed));
    }

    #[Test]
    public function active_to_cancelled_allowed(): void
    {
        $this->assertTrue(OnboardingPlanStatus::Active->canTransitionTo(OnboardingPlanStatus::Cancelled));
    }

    #[Test]
    public function terminal_transitions_blocked(): void
    {
        $this->assertFalse(OnboardingPlanStatus::Completed->canTransitionTo(OnboardingPlanStatus::Active));
        $this->assertFalse(OnboardingPlanStatus::Completed->canTransitionTo(OnboardingPlanStatus::Cancelled));
        $this->assertFalse(OnboardingPlanStatus::Cancelled->canTransitionTo(OnboardingPlanStatus::Draft));
        $this->assertFalse(OnboardingPlanStatus::Cancelled->canTransitionTo(OnboardingPlanStatus::Active));
    }
}
