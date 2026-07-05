<?php

namespace App\Modules\Asset\Infrastructure\Seeders;

use App\Modules\Asset\Infrastructure\Persistence\Eloquent\Models\AssetItemModel;
use Illuminate\Database\Seeder;

class DemoAssetSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['asset_code' => 'LAP-0001', 'asset_type' => 'laptop', 'name' => 'MacBook Pro 14 M3', 'serial_number' => 'SN-MBP14-001', 'condition' => 'good', 'status' => 'available'],
            ['asset_code' => 'LAP-0002', 'asset_type' => 'laptop', 'name' => 'Dell XPS 15', 'serial_number' => 'SN-XPS15-002', 'condition' => 'good', 'status' => 'available'],
            ['asset_code' => 'LAP-0003', 'asset_type' => 'laptop', 'name' => 'Lenovo ThinkPad X1', 'serial_number' => 'SN-TPX1-003', 'condition' => 'good', 'status' => 'available'],
            ['asset_code' => 'MON-0001', 'asset_type' => 'monitor', 'name' => 'Dell UltraSharp 27"', 'serial_number' => 'SN-DEL27-001', 'condition' => 'good', 'status' => 'available'],
            ['asset_code' => 'MON-0002', 'asset_type' => 'monitor', 'name' => 'LG UltraFine 4K', 'serial_number' => 'SN-LG4K-002', 'condition' => 'good', 'status' => 'available'],
            ['asset_code' => 'PHN-0001', 'asset_type' => 'phone', 'name' => 'iPhone 15 Pro', 'serial_number' => 'SN-IP15P-001', 'condition' => 'good', 'status' => 'available'],
            ['asset_code' => 'KEY-0001', 'asset_type' => 'accessory', 'name' => 'Logitech MX Keys', 'serial_number' => 'SN-MXK-001', 'condition' => 'good', 'status' => 'available'],
            ['asset_code' => 'MOU-0001', 'asset_type' => 'accessory', 'name' => 'Logitech MX Master 3', 'serial_number' => 'SN-MXM3-001', 'condition' => 'good', 'status' => 'available'],
        ];

        foreach ($items as $item) {
            AssetItemModel::updateOrCreate(['asset_code' => $item['asset_code']], $item);
        }
    }
}
