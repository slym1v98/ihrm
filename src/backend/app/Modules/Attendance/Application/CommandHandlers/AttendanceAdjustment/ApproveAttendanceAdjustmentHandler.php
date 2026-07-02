<?php

namespace App\Modules\Attendance\Application\CommandHandlers\AttendanceAdjustment;

use App\Modules\Attendance\Application\Commands\AttendanceAdjustment\ApproveAttendanceAdjustmentCommand;
use App\Modules\Attendance\Domain\Repositories\AttendanceAdjustmentRequestRepositoryInterface;
use Carbon\CarbonImmutable;

class ApproveAttendanceAdjustmentHandler
{
    public function __construct(
        private AttendanceAdjustmentRequestRepositoryInterface $adjustmentRepo,
    ) {}

    public function handle(ApproveAttendanceAdjustmentCommand $command): void
    {
        $adjustment = $this->adjustmentRepo->findById($command->adjustmentId);
        $adjustment->approve($command->approverId, CarbonImmutable::now());
        $this->adjustmentRepo->saveAndDispatch($adjustment);
    }
}
