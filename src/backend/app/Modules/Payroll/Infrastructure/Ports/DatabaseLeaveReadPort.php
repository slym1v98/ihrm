<?php

namespace App\Modules\Payroll\Infrastructure\Ports;

use App\Modules\Payroll\Domain\Ports\LeaveReadPort;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

class DatabaseLeaveReadPort implements LeaveReadPort
{
    public function getLeaveForEmployee(string $employeeId, DateTimeImmutable $start, DateTimeImmutable $end): array
    {
        $rows = DB::table('leave_requests')
            ->selectRaw('COALESCE(SUM(duration_minutes), 0) as total_minutes')
            ->where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->whereDate('start_at', '<=', $end->format('Y-m-d'))
            ->whereDate('end_at', '>=', $start->format('Y-m-d'))
            ->first();

        $totalDays = $rows ? ((float) $rows->total_minutes / 480) : 0.0;

        // Simplified: all leave is paid if approved (unpaid handling deferred to leave type config)
        return ['paid_days' => $totalDays, 'unpaid_days' => 0.0];
    }
}
