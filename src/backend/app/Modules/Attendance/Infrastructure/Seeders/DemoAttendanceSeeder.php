<?php

namespace App\Modules\Attendance\Infrastructure\Seeders;

use App\Modules\Attendance\Infrastructure\Persistence\Eloquent\AttendancePeriodModel;
use App\Modules\Attendance\Infrastructure\Persistence\Eloquent\AttendanceTimesheetModel;
use App\Modules\Employee\Infrastructure\Persistence\Eloquent\EmployeeModel;
use Illuminate\Database\Seeder;

class DemoAttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $period = AttendancePeriodModel::where('status', 'closed')->first();
        $employees = EmployeeModel::where('status', 'active')->limit(6)->pluck('id');

        if (!$period || $employees->isEmpty()) return;

        // Generate 10 working days of timesheets for each employee in the closed period
        $start = \Carbon\CarbonImmutable::parse($period->start_date);
        $end = \Carbon\CarbonImmutable::parse($period->end_date);

        foreach ($employees as $empId) {
            $date = $start;
            $count = 0;
            while ($date <= $end && $count < 10) {
                if ($date->isWeekday()) {
                    AttendanceTimesheetModel::firstOrCreate(
                        ['employee_id' => $empId, 'work_date' => $date->toDateString()],
                        [
                            'attendance_period_id' => $period->id,
                            'expected_minutes' => 480,
                            'worked_minutes' => rand(420, 540),
                            'late_minutes' => rand(0, 30),
                            'early_leave_minutes' => rand(0, 15),
                            'overtime_minutes' => rand(0, 120),
                            'result_status' => 'present',
                        ],
                    );
                    $count++;
                }
                $date = $date->addDay();
            }
        }
    }
}
