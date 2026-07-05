<?php

namespace App\Modules\Leave\Application\Workflow;

use App\Modules\Employee\Domain\Aggregates\Employee\EmployeeId;
use App\Modules\Employee\Domain\Repositories\EmployeeRepositoryInterface;
use App\Modules\Leave\Domain\Aggregates\LeaveRequest\LeaveRequestId;
use App\Modules\Leave\Domain\Repositories\LeaveRequestRepositoryInterface;
use App\Modules\Leave\Domain\Repositories\LeaveTypeRepositoryInterface;
use App\Modules\Workflow\Application\Contracts\SubjectDataProvider;

final readonly class LeaveRequestSubjectProvider implements SubjectDataProvider
{
    public function __construct(
        private LeaveRequestRepositoryInterface $leaveRequests,
        private LeaveTypeRepositoryInterface $leaveTypes,
        private EmployeeRepositoryInterface $employees,
    ) {}

    public function subjectType(): string
    {
        return 'leave_request';
    }

    public function fetchContext(string $subjectId): array
    {
        $request = $this->leaveRequests->findById(new LeaveRequestId($subjectId));
        if ($request === null) {
            return [];
        }

        $type = $this->leaveTypes->findById($request->leaveTypeId());
        $employee = $this->employees->findById(EmployeeId::fromString($request->employeeId()));
        $manager = $employee?->managerId() ? $this->employees->findById($employee->managerId()) : null;

        return [
            'subject_id' => $request->id()->value(),
            'employee_id' => $request->employeeId(),
            'leave_type_id' => $request->leaveTypeId()->value(),
            'leave_type_code' => $type?->code(),
            'duration_days' => (int) ceil($request->period()->durationMinutes() / 480),
            'duration_minutes' => $request->period()->durationMinutes(),
            'start_at' => $request->period()->startAt()->toDateString(),
            'end_at' => $request->period()->endAt()->toDateString(),
            'manager_id' => $manager?->userId(),
            'department_id' => $employee?->departmentId(),
            'branch_id' => $employee?->branchId(),
        ];
    }
}
