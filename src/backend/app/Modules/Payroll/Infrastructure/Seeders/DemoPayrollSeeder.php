<?php

namespace App\Modules\Payroll\Infrastructure\Seeders;

use App\Modules\Employee\Infrastructure\Persistence\Eloquent\ContractModel;
use App\Modules\Employee\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Modules\Identity\Infrastructure\Persistence\Eloquent\UserModel;
use App\Modules\Payroll\Infrastructure\Persistence\Eloquent\PayrollComponentModel;
use App\Modules\Payroll\Infrastructure\Persistence\Eloquent\PayrollEntryLineModel;
use App\Modules\Payroll\Infrastructure\Persistence\Eloquent\PayrollEntryModel;
use App\Modules\Payroll\Infrastructure\Persistence\Eloquent\PayrollPeriodModel;
use App\Modules\Payroll\Infrastructure\Persistence\Eloquent\PayrollRunModel;
use App\Modules\Payroll\Infrastructure\Persistence\Eloquent\PayslipModel;
use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;

class DemoPayrollSeeder extends Seeder
{
    public function run(): void
    {
        $admin = UserModel::where('email', 'admin@ihrm.local')->first();
        if (!$admin) return;

        // ── One closed period + one open period ─────────────────────────
        $closed = $this->period('T05-2026', '2026-05-01', '2026-05-31', '2026-06-03', 'locked', $admin->id, true);
        $open   = $this->period('T06-2026', '2026-06-01', '2026-06-30', '2026-07-03', 'open',   $admin->id, false);

        // Only build entries+payslips for the closed period
        $components = PayrollComponentModel::pluck('id', 'code')->all();
        if (!isset($components['base_salary'], $components['meal_allowance'], $components['net_pay'])) return;

        $run = PayrollRunModel::create([
            'id'              => (string) Uuid::uuid4(),
            'period_id'       => $closed->id,
            'run_type'        => 'initial',
            'status'          => 'completed',
            'formula_version' => 'v1',
            'triggered_by'    => $admin->id,
            'started_at'      => '2026-06-01 08:00:00',
            'completed_at'    => '2026-06-01 08:05:00',
        ]);

        $employees = EmployeeModel::where('status', 'active')->limit(6)->get();
        foreach ($employees as $emp) {
            $contract = ContractModel::where('employee_id', $emp->id)->where('status', 'active')->first();
            $base = $contract ? (float) $contract->base_salary : 15_000_000;
            $meal = 730_000;
            $gross = $base + $meal;
            $bhxh  = round($base * 0.08, 2);
            $bhyt  = round($base * 0.015, 2);
            $bhtn  = round($base * 0.01, 2);
            $ded   = $bhxh + $bhyt + $bhtn;
            $net   = $gross - $ded;

            $entry = PayrollEntryModel::create([
                'id'                  => (string) Uuid::uuid4(),
                'run_id'              => $run->id,
                'period_id'           => $closed->id,
                'employee_id'         => $emp->id,
                'contract_snapshot'   => ['base_salary' => $base],
                'attendance_snapshot' => ['working_days' => 22],
                'leave_snapshot'      => ['used' => 0],
                'gross_amount'        => $gross,
                'deduction_amount'    => $ded,
                'net_amount'          => $net,
                'status'              => 'approved',
                'reviewed_by'         => $admin->id,
                'reviewed_at'         => '2026-06-02 10:00:00',
            ]);

            $lines = [
                ['base_salary',     'base',      $base],
                ['meal_allowance',  'allowance', $meal],
                ['social_insurance','insurance', -$bhxh],
                ['health_insurance','insurance', -$bhyt],
                ['unemployment_insurance','insurance', -$bhtn],
                ['net_pay',         'net',       $net],
            ];
            foreach ($lines as [$code, $cat, $amount]) {
                if (!isset($components[$code])) continue;
                PayrollEntryLineModel::create([
                    'entry_id'     => $entry->id,
                    'component_id' => $components[$code],
                    'category'     => $cat,
                    'amount'       => $amount,
                ]);
            }

            PayslipModel::create([
                'id'           => (string) Uuid::uuid4(),
                'entry_id'     => $entry->id,
                'employee_id'  => $emp->id,
                'period_id'    => $closed->id,
                'gross'        => $gross,
                'deductions'   => $ded,
                'net'          => $net,
                'payload'      => ['lines' => $lines],
                'status'       => 'published',
                'published_at' => '2026-06-05 09:00:00',
            ]);
        }
    }

    private function period(string $code, string $start, string $end, string $cutoff, string $status, string $openedBy, bool $locked): PayrollPeriodModel
    {
        return PayrollPeriodModel::updateOrCreate(
            ['period_code' => $code],
            [
                'id'          => (string) Uuid::uuid4(),
                'start_date'  => $start,
                'end_date'    => $end,
                'cutoff_date' => $cutoff,
                'status'      => $status,
                'opened_by'   => $openedBy,
                'opened_at'   => $start . ' 00:00:00',
                'approved_by' => $locked ? $openedBy : null,
                'approved_at' => $locked ? $cutoff . ' 10:00:00' : null,
                'locked_by'   => $locked ? $openedBy : null,
                'locked_at'   => $locked ? $cutoff . ' 12:00:00' : null,
                'published_at'=> $locked ? $cutoff . ' 15:00:00' : null,
            ],
        );
    }
}
