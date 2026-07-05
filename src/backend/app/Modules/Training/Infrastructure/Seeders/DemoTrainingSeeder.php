<?php

namespace App\Modules\Training\Infrastructure\Seeders;

use App\Modules\Training\Infrastructure\Persistence\Eloquent\TrainingCourseModel;
use Illuminate\Database\Seeder;

class DemoTrainingSeeder extends Seeder
{
    public function run(): void
    {
        $courses = [
            ['code' => 'ONBRD-01', 'name' => 'Định hướng nhân viên mới', 'description' => 'Giới thiệu công ty, quy trình, văn hoá', 'category' => 'onboarding', 'default_duration_hours' => 8, 'max_participants' => 30, 'active' => true],
            ['code' => 'SAFETY-01', 'name' => 'An toàn lao động', 'description' => 'Đào tạo an toàn bắt buộc', 'category' => 'compliance', 'default_duration_hours' => 4, 'max_participants' => 50, 'active' => true],
            ['code' => 'LEAD-01', 'name' => 'Kỹ năng lãnh đạo cơ bản', 'description' => 'Cho quản lý cấp trung', 'category' => 'leadership', 'default_duration_hours' => 16, 'max_participants' => 20, 'active' => true],
            ['code' => 'TECH-PHP', 'name' => 'PHP nâng cao', 'description' => 'Laravel & DDD patterns', 'category' => 'technical', 'default_duration_hours' => 24, 'max_participants' => 15, 'active' => true],
            ['code' => 'SOFT-01', 'name' => 'Kỹ năng giao tiếp', 'description' => 'Kỹ năng mềm cơ bản', 'category' => 'soft-skill', 'default_duration_hours' => 8, 'max_participants' => 25, 'active' => true],
        ];

        foreach ($courses as $c) {
            TrainingCourseModel::updateOrCreate(['code' => $c['code']], $c);
        }
    }
}
