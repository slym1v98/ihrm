<?php

namespace App\Modules\Payroll\Infrastructure\Seeders;

use App\Modules\Payroll\Infrastructure\Persistence\Eloquent\PayrollComponentModel;
use Illuminate\Database\Seeder;

class PayrollComponentSeeder extends Seeder
{
    public function run(): void
    {
        // Pass 1: fixed-amount and manual-entry components
        $components = [
            ['code'=>'base_salary','name'=>'Lương cơ bản','category'=>'base','calculation_type'=>'fixed_amount','default_amount'=>0,'taxable'=>true],
            ['code'=>'meal_allowance','name'=>'Phụ cấp ăn trưa','category'=>'allowance','calculation_type'=>'fixed_amount','default_amount'=>730000,'taxable'=>false],
            ['code'=>'travel_allowance','name'=>'Phụ cấp đi lại','category'=>'allowance','calculation_type'=>'fixed_amount','default_amount'=>200000,'taxable'=>false],
            ['code'=>'overtime_pay','name'=>'Lương tăng ca','category'=>'overtime','calculation_type'=>'manual_entry','default_amount'=>0,'taxable'=>true],
            ['code'=>'bonus','name'=>'Thưởng','category'=>'bonus','calculation_type'=>'manual_entry','default_amount'=>0,'taxable'=>true],
            ['code'=>'penalty','name'=>'Phạt','category'=>'penalty','calculation_type'=>'manual_entry','default_amount'=>0,'taxable'=>false],
            ['code'=>'other_deduction','name'=>'Khấu trừ khác','category'=>'deduction','calculation_type'=>'manual_entry','default_amount'=>0,'taxable'=>false],
            ['code'=>'net_pay','name'=>'Lương thực nhận','category'=>'net','calculation_type'=>'manual_entry','default_amount'=>0,'taxable'=>false],
        ];

        foreach ($components as $c) {
            PayrollComponentModel::updateOrCreate(
                ['code' => $c['code']],
                $c
            );
        }

        // Pass 2: percent_of_component (look up base_salary from DB)
        $base = PayrollComponentModel::where('code', 'base_salary')->first();
        $baseId = $base?->id;

        $percentComponents = [
            ['code'=>'position_allowance','name'=>'Phụ cấp chức vụ','category'=>'allowance','calculation_type'=>'percent_of_component','default_percent'=>10,'taxable'=>true],
            ['code'=>'social_insurance','name'=>'Bảo hiểm xã hội','category'=>'insurance','calculation_type'=>'percent_of_component','default_percent'=>8,'taxable'=>false],
            ['code'=>'health_insurance','name'=>'Bảo hiểm y tế','category'=>'insurance','calculation_type'=>'percent_of_component','default_percent'=>1.5,'taxable'=>false],
            ['code'=>'unemployment_insurance','name'=>'Bảo hiểm thất nghiệp','category'=>'insurance','calculation_type'=>'percent_of_component','default_percent'=>1,'taxable'=>false],
            ['code'=>'income_tax','name'=>'Thuế TNCN','category'=>'tax','calculation_type'=>'percent_of_component','default_percent'=>10,'taxable'=>false],
        ];

        foreach ($percentComponents as $c) {
            PayrollComponentModel::updateOrCreate(
                ['code' => $c['code']],
                array_merge($c, ['percent_base_component_id' => $baseId, 'active' => true])
            );
        }
    }
}
