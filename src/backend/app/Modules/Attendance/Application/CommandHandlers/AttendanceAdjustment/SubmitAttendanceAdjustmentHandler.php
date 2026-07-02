<?php

namespace App\Modules\Attendance\Application\CommandHandlers\AttendanceAdjustment;

use App\Modules\Attendance\Application\Commands\AttendanceAdjustment\SubmitAttendanceAdjustmentCommand;
use App\Modules\Attendance\Domain\Aggregates\AttendanceAdjustmentRequest\AttendanceAdjustmentRequest;
use App\Modules\Attendance\Domain\Repositories\AttendanceAdjustmentRequestRepositoryInterface;
use App\Modules\Attendance\Domain\Repositories\AttendanceTimesheetRepositoryInterface;
use App\Modules\Attendance\Domain\Repositories\AttendancePeriodRepositoryInterface;

class SubmitAttendanceAdjustmentHandler
{
    public function __construct(
        private AttendanceAdjustmentRequestRepositoryInterface $adjustmentRepo,
        private AttendanceTimesheetRepositoryInterface $timesheetRepo,
        private AttendancePeriodRepositoryInterface $periodRepo,
    ) {}

    public function handle(SubmitAttendanceAdjustmentCommand $command): AttendanceAdjustmentRequest
    {
        $timesheet = $this->timesheetRepo->findById($command->attendanceTimesheetId);
        // Timesheet existence check handled by controller validation

        $request = AttendanceAdjustmentRequest::submit(
            timesheetId: $command->attendanceTimesheetId,
            employeeId: $command->employeeId,
            requestedBy: $command->requestedBy,
            corrections: $command->corrections,
            reason: $command->reason,
            evidenceFile: $command->evidenceFile,
        );

        $this->adjustmentRepo->saveAndDispatch($request);

        return $request;
    }
}
