<?php

namespace App\Modules\Performance\Infrastructure\Seeders;

use App\Modules\Performance\Infrastructure\Persistence\Eloquent\PerformanceCycleModel;
use App\Modules\Performance\Infrastructure\Persistence\Eloquent\CompetencyTemplateModel;
use Illuminate\Database\Seeder;

class DemoPerformanceSeeder extends Seeder
{
    public function run(): void
    {
        PerformanceCycleModel::updateOrCreate(
            ['code' => 'REVIEW-H1-2026'],
            [
                'name' => 'Đánh giá H1/2026',
                'description' => 'Đánh giá 6 tháng đầu năm',
                'start_date' => '2026-01-01',
                'end_date' => '2026-06-30',
                'status' => 'open',
                'scoring_rules' => [],
            ]
        );

        $competencies = [
            ['code' => 'COMP-TECH', 'name' => 'Năng lực chuyên môn', 'rules' => ['levels' => [1,2,3,4,5], 'weight' => 40], 'active' => true],
            ['code' => 'COMP-SOFT', 'name' => 'Kỹ năng mềm', 'rules' => ['levels' => [1,2,3,4,5], 'weight' => 30], 'active' => true],
            ['code' => 'COMP-MGMT', 'name' => 'Quản lý', 'rules' => ['levels' => [1,2,3,4,5], 'weight' => 30], 'active' => true],
        ];

        foreach ($competencies as $c) {
            CompetencyTemplateModel::updateOrCreate(['code' => $c['code']], $c);
        }
    }
}
