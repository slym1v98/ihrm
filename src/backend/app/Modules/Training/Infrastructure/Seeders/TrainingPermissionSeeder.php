<?php
namespace App\Modules\Training\Infrastructure\Seeders;
use Illuminate\Database\Seeder; use App\Modules\Identity\Infrastructure\Persistence\Eloquent\PermissionModel; use App\Modules\Identity\Infrastructure\Persistence\Eloquent\RoleModel; use App\Modules\Identity\Infrastructure\Persistence\Eloquent\RolePermissionModel;
class TrainingPermissionSeeder extends Seeder {
    public function run(): void {
        $ps=[
            ['training.course.view','course','view'],['training.course.create','course','create'],['training.course.update','course','update'],['training.course.delete','course','delete'],
            ['training.session.view','session','view'],['training.session.create','session','create'],['training.session.update','session','update'],
            ['training.enrollment.view','enrollment','view'],['training.enrollment.create','enrollment','create'],['training.enrollment.cancel','enrollment','cancel'],
            ['training.result.view','result','view'],['training.result.create','result','create'],
        ];
        $codes=[]; foreach($ps as [$code,$m,$a]) { $p=PermissionModel::firstOrCreate(['code'=>$code],['module'=>$m,'action'=>$a,'description'=>"$m.$a"]); $codes[]=$p->code; }
        RoleModel::where('code','SUPER_ADMIN')->each(fn($r)=>array_map(fn($c)=>RolePermissionModel::firstOrCreate(['role_id'=>$r->id,'permission_code'=>$c]),$codes));
    }
}
