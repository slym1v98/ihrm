<?php

namespace App\Modules\Leave\Infrastructure\Seeders;

use App\Modules\Employee\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Modules\Leave\Infrastructure\Persistence\Eloquent\LeaveBalanceModel;
use App\Modules\Leave\Infrastructure\Persistence\Eloquent\LeavePolicyModel;
use App\Modules\Leave\Infrastructure\Persistence\Eloquent\LeaveRequestModel;
use App\Modules\Leave\Infrastructure\Persistence\Eloquent\LeaveTypeModel;
use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;

class DemoLeaveSeeder extends Seeder
{
    public function run(): void
    {
        $types = LeaveTypeModel::pluck('id', 'code')->all();
        $employees = EmployeeModel::where('status', 'active')->pluck('id')->all();
        $year = 2026;

        // ── Leave policies ─────────────────────────────────────────────
        $policies = [
            ['annual',    '2026-01-01', null,         null, false, 5,  6,  true,  false],
            ['sick',      '2026-01-01', null,         10,   true,  null, null, true, false],
            ['maternity', '2026-01-01', null,         null, true,  null, null, false, false],
            ['unpaid',    '2026-01-01', null,         null, false, null, null, true, false],
        ];
        foreach ($policies as [$code, $from, $until, $maxConsec, $attach, $carry, $carryExp, $half, $hourly]) {
            $tid = $types[$code] ?? null;
            if (! $tid) {
                continue;
            }
            LeavePolicyModel::updateOrCreate(
                ['leave_type_id' => $tid, 'valid_from' => $from],
                [
                    'valid_until' => $until,
                    'max_consecutive_days' => $maxConsec,
                    'requires_attachment' => $attach,
                    'carry_over_limit' => $carry,
                    'carry_over_expiry_months' => $carryExp,
                    'half_day_allowed' => $half,
                    'hourly_allowed' => $hourly,
                ],
            );
        }

        // ── Leave balances: every employee, annual + sick ───────────────
        foreach ($employees as $empId) {
            foreach (['annual' => 12, 'sick' => 10] as $code => $days) {
                $tid = $types[$code] ?? null;
                if (! $tid) {
                    continue;
                }
                LeaveBalanceModel::updateOrCreate(
                    ['employee_id' => $empId, 'leave_type_id' => $tid, 'year' => $year],
                    [
                        'opening' => 0,
                        'accrued' => $days,
                        'used' => 0,
                        'carried_over' => 0,
                        'expired' => 0,
                    ],
                );
            }
        }

        if (empty($employees)) {
            return;
        }

        $adminId = $employees[0];

        // ── Sample leave requests ───────────────────────────────────────
        $samples = [
            // pending
            [$employees[0] ?? null, 'annual', '2026-07-10', '2026-07-11', 'day',  960,  'pending',  null],
            [$employees[1] ?? null, 'sick',   '2026-07-14', '2026-07-14', 'day',  480,  'pending',  null],
            // approved
            [$employees[2] ?? null, 'annual', '2026-06-05', '2026-06-06', 'day',  960,  'approved', $adminId],
            [$employees[3] ?? null, 'annual', '2026-06-12', '2026-06-12', 'day',  480,  'approved', $adminId],
            [$employees[4] ?? null, 'sick',   '2026-06-20', '2026-06-20', 'day',  480,  'approved', $adminId],
            // rejected
            [$employees[5] ?? null, 'unpaid', '2026-07-01', '2026-07-03', 'day',  1440, 'rejected', $adminId],
        ];

        foreach ($samples as [$empId, $typeCode, $start, $end, $unit, $minutes, $status, $approver]) {
            if (! $empId) {
                continue;
            }
            $tid = $types[$typeCode] ?? null;
            if (! $tid) {
                continue;
            }
            LeaveRequestModel::create([
                'id' => (string) Uuid::uuid4(),
                'employee_id' => $empId,
                'leave_type_id' => $tid,
                'start_at' => $start,
                'end_at' => $end,
                'duration_unit' => $unit,
                'duration_minutes' => $minutes,
                'reason' => 'Lý do cá nhân',
                'status' => $status,
                'approved_by' => $approver,
                'approved_at' => $approver ? now() : null,
                'balance_before' => 12,
            ]);
        }
    }
}
