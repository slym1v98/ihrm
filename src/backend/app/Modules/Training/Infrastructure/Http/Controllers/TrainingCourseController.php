<?php
namespace App\Modules\Training\Infrastructure\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Modules\Training\Application\Commands\{CreateCourseCommand,UpdateCourseCommand,DeactivateCourseCommand};
use App\Modules\Training\Application\CommandHandlers\{CreateCourseHandler,UpdateCourseHandler,DeactivateCourseHandler};
use App\Modules\Training\Application\Queries\ListCoursesQuery; use App\Modules\Training\Application\QueryHandlers\ListCoursesHandler;
use App\Modules\Training\Domain\Aggregates\TrainingCourse\TrainingCourseId; use App\Modules\Training\Domain\Repositories\TrainingCourseRepositoryInterface; use App\Modules\Training\Domain\Exceptions\TrainingCourseNotFoundException;
use Illuminate\Http\JsonResponse; use Illuminate\Http\Request;
class TrainingCourseController extends Controller {
    public function __construct(private readonly CreateCourseHandler $createHandler, private readonly UpdateCourseHandler $updateHandler, private readonly DeactivateCourseHandler $deactivateHandler, private readonly ListCoursesHandler $listHandler, private readonly TrainingCourseRepositoryInterface $courseRepo) {}
    public function index(Request $r): JsonResponse { $items=$this->listHandler->handle(new ListCoursesQuery($r->boolean('active'))); return response()->json(array_map(fn($c)=>['id'=>$c->getId()->value,'code'=>$c->getCode(),'name'=>$c->getName(),'description'=>$c->getDescription(),'category'=>$c->getCategory(),'default_duration_hours'=>$c->getDefaultDurationHours(),'max_participants'=>$c->getMaxParticipants(),'active'=>$c->isActive()],$items)); }
    public function store(Request $r): JsonResponse { $c=$this->createHandler->handle(new CreateCourseCommand($r->input('code'),$r->input('name'),$r->input('description'),$r->input('category'),$r->integer('default_duration_hours') ?: null,$r->integer('max_participants') ?: null)); return response()->json(['id'=>$c->getId()->value],201); }
    public function show(string $id): JsonResponse { $c=$this->courseRepo->findById(TrainingCourseId::fromString($id)) ?? throw new TrainingCourseNotFoundException($id); return response()->json(['id'=>$c->getId()->value,'code'=>$c->getCode(),'name'=>$c->getName(),'description'=>$c->getDescription(),'category'=>$c->getCategory(),'default_duration_hours'=>$c->getDefaultDurationHours(),'max_participants'=>$c->getMaxParticipants(),'active'=>$c->isActive()]); }
    public function update(Request $r, string $id): JsonResponse { $this->updateHandler->handle(new UpdateCourseCommand($id,$r->input('code'),$r->input('name'),$r->input('description'),$r->input('category'),$r->integer('default_duration_hours') ?: null,$r->integer('max_participants') ?: null)); return response()->json(['message'=>'Updated']); }
    public function destroy(string $id): JsonResponse { try{$this->deactivateHandler->handle(new DeactivateCourseCommand($id)); return response()->json(['message'=>'Deactivated']);}catch(\Exception $e){return response()->json(['error'=>$e->getMessage()],422);} }
}
