<?php

namespace App\Modules\Offboarding\Infrastructure\Services;

use App\Modules\Offboarding\Domain\Events\OffboardingCompleted;
use App\Modules\Offboarding\Domain\Events\OffboardingTaskAssigned;
use App\Modules\Offboarding\Domain\Events\OffboardingTaskCompleted;

class NotificationService
{
    public function notifyTaskAssigned(string $ownerType, string $ownerId, string $taskTitle, \DateTimeImmutable $dueDate): void
    {
        // Event-based notification — listeners handle delivery
        event(new OffboardingTaskAssigned(null, null, $ownerType, $ownerId, $dueDate));
    }

    public function notifyTaskCompleted(string $taskId, string $planId): void
    {
        event(new OffboardingTaskCompleted(null, $planId, null));
    }

    public function notifyPlanCompleted(string $planId, string $employeeId): void
    {
        event(new OffboardingCompleted($planId, $employeeId));
    }
}
