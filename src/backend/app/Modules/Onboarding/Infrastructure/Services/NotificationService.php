<?php

namespace App\Modules\Onboarding\Infrastructure\Services;

use App\Modules\Onboarding\Domain\Events\OnboardingCompleted;
use App\Modules\Onboarding\Domain\Events\OnboardingTaskAssigned;
use App\Modules\Onboarding\Domain\Events\OnboardingTaskCompleted;

class NotificationService
{
    public function notifyTaskAssigned(string $ownerType, string $ownerId, string $taskTitle, \DateTimeImmutable $dueDate): void
    {
        // Event-based notification — listeners handle delivery
        event(new OnboardingTaskAssigned(null, null, $ownerType, $ownerId, $dueDate));
    }

    public function notifyTaskCompleted(string $taskId, string $planId): void
    {
        event(new OnboardingTaskCompleted(null, $planId, null));
    }

    public function notifyPlanCompleted(string $planId, string $employeeId): void
    {
        event(new OnboardingCompleted($planId, $employeeId));
    }
}
