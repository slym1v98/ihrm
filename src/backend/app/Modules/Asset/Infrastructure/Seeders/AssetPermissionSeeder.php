<?php

namespace App\Modules\Asset\Infrastructure\Seeders;

use App\Modules\Identity\Infrastructure\Persistence\Eloquent\PermissionModel;
use App\Modules\Identity\Infrastructure\Persistence\Eloquent\RoleModel;
use App\Modules\Identity\Infrastructure\Persistence\Eloquent\RolePermissionModel;
use Illuminate\Database\Seeder;

class AssetPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $ps = [
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
        $codes = [];
        foreach ($ps as [$code, $module, $action]) {
            $p = PermissionModel::firstOrCreate(
                ['code' => $code],
                ['module' => $module, 'action' => $action, 'description' => "$module.$action"]
            );
            $codes[] = $p->code;
        }
        RoleModel::where('code', 'SUPER_ADMIN')->each(
            fn ($r) => array_map(
                fn ($c) => RolePermissionModel::firstOrCreate(
                    ['role_id' => $r->id, 'permission_code' => $c]
                ),
                $codes
            )
        );
    }
}
