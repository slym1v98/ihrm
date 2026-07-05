<?php

namespace App\Modules\Reporting\Infrastructure\Seeders;

use App\Modules\Reporting\Infrastructure\Persistence\Eloquent\ReportDefinitionModel;
use App\Modules\Reporting\Infrastructure\Persistence\Eloquent\ReportRunModel;
use App\Modules\Identity\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Database\Seeder;

class DemoReportingSeeder extends Seeder
{
    public function run(): void
    {
        $admin = UserModel::where('email', 'admin@ihrm.local')->first();
        $def = ReportDefinitionModel::first();

        if (!$admin || !$def) return;

        ReportRunModel::firstOrCreate(
            ['report_definition_id' => $def->id],
            [
                'requested_by' => $admin->id,
                'filters' => ['branch' => 'all'],
                'status' => 'completed',
                'result' => 'reports/headcount-2026-06.csv',
                'started_at' => now()->subDay(),
                'completed_at' => now()->subDay()->addMinutes(5),
            ],
        );
    }
}
