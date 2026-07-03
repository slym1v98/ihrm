<?php
namespace App\Modules\Asset\Infrastructure\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssetPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['asset.item.view', 'item', 'view'],
            ['asset.item.create', 'item', 'create'],
            ['asset.item.update', 'item', 'update'],
            ['asset.item.delete', 'item', 'delete'],
            ['asset.item.mark-status', 'item', 'mark-status'],
            ['asset.assignment.view', 'assignment', 'view'],
            ['asset.assignment.create', 'assignment', 'create'],
            ['asset.assignment.return', 'assignment', 'return'],
            ['asset.obligation.view', 'obligation', 'view'],
        ];
        foreach ($permissions as [$code, $resource, $action]) {
            DB::table('permissions')->updateOrInsert(
                ['code' => $code],
                ['resource' => $resource, 'action' => $action, 'created_at' => now(), 'updated_at' => now()],
            );
        }
    }
}
