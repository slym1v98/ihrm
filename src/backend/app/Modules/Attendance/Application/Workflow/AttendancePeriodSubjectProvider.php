<?php

namespace App\Modules\Attendance\Application\Workflow;

use App\Modules\Attendance\Domain\Repositories\AttendancePeriodRepositoryInterface;
use App\Modules\Workflow\Application\Contracts\SubjectDataProvider;

final readonly class AttendancePeriodSubjectProvider implements SubjectDataProvider
{
    public function __construct(
        private AttendancePeriodRepositoryInterface $periods,
    ) {}

    public function subjectType(): string
    {
        return 'attendance_period';
    }

    public function fetchContext(string $subjectId): array
    {
        return ['subject_id' => $subjectId];
    }
}
