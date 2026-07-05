<?php

namespace App\Modules\Leave\Infrastructure\Seeders;

use App\Modules\Leave\Infrastructure\Persistence\Eloquent\LeaveTypeModel;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['code' => 'annual', 'name' => 'Annual Leave', 'is_balance_tracked' => true, 'sort_order' => 10],
            ['code' => 'sick', 'name' => 'Sick Leave', 'is_balance_tracked' => false, 'sort_order' => 20],
            ['code' => 'unpaid', 'name' => 'Unpaid Leave', 'is_balance_tracked' => false, 'sort_order' => 30],
            ['code' => 'maternity', 'name' => 'Maternity Leave', 'is_balance_tracked' => true, 'sort_order' => 40],
        ];

        foreach ($types as $t) {
            LeaveTypeModel::updateOrCreate(
                ['code' => $t['code']],
                ['name' => $t['name'], 'is_balance_tracked' => $t['is_balance_tracked'], 'is_active' => true, 'sort_order' => $t['sort_order']],
            );
        }
    }
}
