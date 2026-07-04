<?php

namespace App\Modules\Payroll\Infrastructure\Http\Controllers;

use App\Modules\Payroll\Infrastructure\Persistence\Eloquent\PayrollEntryModel;
use App\Modules\Payroll\Infrastructure\Persistence\Eloquent\PayrollPeriodModel;
use Illuminate\Http\JsonResponse;

class PayrollSummaryController
{
    public function show(string $periodId): JsonResponse
    {
        $period = PayrollPeriodModel::findOrFail($periodId);
        $summary = PayrollEntryModel::where('period_id', $periodId)
            ->selectRaw('count(*) as employee_count')
            ->selectRaw('coalesce(sum(gross_amount),0) as total_gross')
            ->selectRaw('coalesce(sum(deduction_amount),0) as total_deductions')
            ->selectRaw('coalesce(sum(net_amount),0) as total_net')
            ->first();

        return response()->json(['data' => [
            'employee_count' => (int) $summary->employee_count,
            'total_gross' => (float) $summary->total_gross,
            'total_deductions' => (float) $summary->total_deductions,
            'total_net' => (float) $summary->total_net,
            'status' => $period->status,
            'locked_at' => $period->locked_at,
            'period_code' => $period->period_code,
        ]]);
    }
}
