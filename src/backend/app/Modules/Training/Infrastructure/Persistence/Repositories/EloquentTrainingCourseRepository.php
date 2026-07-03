<?php
namespace App\Modules\Training\Infrastructure\Persistence\Repositories;
use App\Modules\Training\Domain\Aggregates\TrainingCourse\TrainingCourse;
use App\Modules\Training\Domain\Aggregates\TrainingCourse\TrainingCourseId;
use App\Modules\Training\Domain\Repositories\TrainingCourseRepositoryInterface;
use App\Modules\Training\Infrastructure\Persistence\Eloquent\TrainingCourseModel;
class EloquentTrainingCourseRepository implements TrainingCourseRepositoryInterface {
    public function findById(TrainingCourseId $id): ?TrainingCourse { $m = TrainingCourseModel::find($id->value); return $m ? $this->toDomain($m) : null; }
    public function findByCode(string $code): ?TrainingCourse { $m = TrainingCourseModel::where('code',$code)->first(); return $m ? $this->toDomain($m) : null; }
    public function all(): array { return TrainingCourseModel::orderByDesc('created_at')->get()->map(fn($m)=>$this->toDomain($m))->toArray(); }
    public function save(TrainingCourse $c): void { TrainingCourseModel::updateOrCreate(['id'=>$c->getId()->value], ['code'=>$c->getCode(),'name'=>$c->getName(),'description'=>$c->getDescription(),'category'=>$c->getCategory(),'default_duration_hours'=>$c->getDefaultDurationHours(),'max_participants'=>$c->getMaxParticipants(),'active'=>$c->isActive()]); }
    private function toDomain(TrainingCourseModel $m): TrainingCourse { return TrainingCourse::reconstitute(TrainingCourseId::fromString($m->id),$m->code,$m->name,$m->description,$m->category,$m->default_duration_hours,$m->max_participants,(bool)$m->active); }
}
