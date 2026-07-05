<?php

namespace App\Modules\Attendance\Infrastructure\Seeders;

use App\Modules\Attendance\Infrastructure\Persistence\Eloquent\AttendancePeriodModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AttendancePeriodSeeder extends Seeder
{
    public function run(): void
    {
        $periods = [
            [
                'id' => Str::uuid()->toString(),
                'period_code' => 'P'.date('Ym'),
                'start_date' => date('Y-m-01'),
                'end_date' => date('Y-m-t'),
                'status' => 'open',
            ],
            [
                'id' => Str::uuid()->toString(),
                'period_code' => 'P'.date('Ym', strtotime('-1 month')),
                'start_date' => date('Y-m-01', strtotime('-1 month')),
                'end_date' => date('Y-m-t', strtotime('-1 month')),
                'status' => 'closed',
            ],
        ];

        foreach ($periods as $p) {
            AttendancePeriodModel::updateOrCreate(['period_code' => $p['period_code']], $p);
        }
    }
}
