<?php

namespace App\Modules\Shift\Infrastructure\Seeders;

use App\Modules\Shift\Infrastructure\Persistence\Eloquent\ShiftAssignmentModel;
use App\Modules\Shift\Infrastructure\Persistence\Eloquent\ShiftTemplateModel;
use App\Modules\Employee\Infrastructure\Persistence\Eloquent\EmployeeModel;
use Illuminate\Database\Seeder;

class DemoShiftSeeder extends Seeder
{
    public function run(): void
    {
        $officeShift = ShiftTemplateModel::where('code', 'SHIFT-OFFICE')->first();
        $morningShift = ShiftTemplateModel::where('code', 'SHIFT-MORNING')->first();
        $employees = EmployeeModel::where('status', 'active')->limit(6)->pluck('id');

        if (!$officeShift || $employees->isEmpty()) return;

        foreach ($employees as $empId) {
            $template = rand(0, 1) ? $officeShift : $morningShift;
            ShiftAssignmentModel::firstOrCreate(
                ['shift_template_id' => $template->id, 'assignable_type' => 'employee', 'assignable_id' => $empId, 'effective_from' => now()->startOfMonth()->subMonth()->toDateString()],
                ['active' => true],
            );
        }
    }
}
