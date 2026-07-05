<?php

namespace App\Modules\Shift\Infrastructure\Seeders;

use App\Modules\Shift\Infrastructure\Persistence\Eloquent\ShiftTemplateModel;
use Illuminate\Database\Seeder;

class ShiftTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            ['code' => 'SHIFT-MORNING', 'name' => 'Ca sáng', 'start_time' => '06:00', 'end_time' => '14:00', 'is_overnight' => false, 'break_minutes' => 30, 'late_tolerance_minutes' => 15, 'active' => true],
            ['code' => 'SHIFT-AFTERNOON', 'name' => 'Ca chiều', 'start_time' => '14:00', 'end_time' => '22:00', 'is_overnight' => false, 'break_minutes' => 30, 'late_tolerance_minutes' => 15, 'active' => true],
            ['code' => 'SHIFT-NIGHT', 'name' => 'Ca đêm', 'start_time' => '22:00', 'end_time' => '06:00', 'is_overnight' => true, 'break_minutes' => 30, 'late_tolerance_minutes' => 15, 'active' => true],
            ['code' => 'SHIFT-OFFICE', 'name' => 'Giờ hành chính', 'start_time' => '08:00', 'end_time' => '17:00', 'is_overnight' => false, 'break_minutes' => 60, 'late_tolerance_minutes' => 10, 'active' => true],
            ['code' => 'SHIFT-FLEX', 'name' => 'Giờ linh hoạt', 'start_time' => '07:00', 'end_time' => '18:00', 'is_overnight' => false, 'break_minutes' => 30, 'late_tolerance_minutes' => 30, 'active' => true],
        ];

        foreach ($templates as $t) {
            ShiftTemplateModel::updateOrCreate(['code' => $t['code']], $t);
        }
    }
}
