<?php

namespace App\Modules\Payroll\Infrastructure\Jobs;

use App\Modules\Payroll\Infrastructure\Persistence\Eloquent\PayrollRunModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CalculatePayrollJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public function __construct(
        private readonly string $payrollRunId,
    ) {}

    public function handle(): void
    {
        $run = PayrollRunModel::find($this->payrollRunId);
        if (! $run) {
            Log::error('Payroll run not found', ['id' => $this->payrollRunId]);

            return;
        }

        $run->update(['status' => 'processing']);
        // ... calculation logic would go here ...
        $run->update(['status' => 'completed']);
    }
}
