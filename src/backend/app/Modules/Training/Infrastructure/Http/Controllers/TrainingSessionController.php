<?php
namespace App\Modules\Training\Infrastructure\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Modules\Training\Application\Commands\{CreateSessionCommand,UpdateSessionCommand};
use App\Modules\Training\Application\CommandHandlers\{CreateSessionHandler,UpdateSessionHandler};
use App\Modules\Training\Application\Queries\ListSessionsQuery; use App\Modules\Training\Application\QueryHandlers\ListSessionsHandler;
use App\Modules\Training\Domain\Aggregates\TrainingSession\TrainingSessionId; use App\Modules\Training\Domain\Repositories\TrainingSessionRepositoryInterface; use App\Modules\Training\Domain\Exceptions\TrainingSessionNotFoundException;
use Illuminate\Http\JsonResponse; use Illuminate\Http\Request;
class TrainingSessionController extends Controller {
    public function __construct(private readonly CreateSessionHandler $createHandler, private readonly UpdateSessionHandler $updateHandler, private readonly ListSessionsHandler $listHandler, private readonly TrainingSessionRepositoryInterface $sessionRepo) {}
    public function index(string $courseId): JsonResponse { $items=$this->listHandler->handle(new ListSessionsQuery($courseId)); return response()->json(array_map(fn($s)=>['id'=>$s->getId()->value,'course_id'=>$s->getCourseId(),'code'=>$s->getCode(),'name'=>$s->getName(),'start_date'=>$s->getStartDate()->format('Y-m-d H:i:s'),'end_date'=>$s->getEndDate()->format('Y-m-d H:i:s'),'location'=>$s->getLocation(),'instructor'=>$s->getInstructor(),'max_participants'=>$s->getMaxParticipants(),'status'=>$s->getStatus()->value],$items)); }
    public function store(Request $r, string $courseId): JsonResponse { $s=$this->createHandler->handle(new CreateSessionCommand($courseId,$r->input('code'),$r->input('name'),$r->input('start_date'),$r->input('end_date'),$r->input('location'),$r->input('instructor'),$r->integer('max_participants') ?: null)); return response()->json(['id'=>$s->getId()->value],201); }
    public function show(string $id): JsonResponse { $s=$this->sessionRepo->findById(TrainingSessionId::fromString($id)) ?? throw new TrainingSessionNotFoundException($id); return response()->json(['id'=>$s->getId()->value,'course_id'=>$s->getCourseId(),'code'=>$s->getCode(),'name'=>$s->getName(),'start_date'=>$s->getStartDate()->format('Y-m-d H:i:s'),'end_date'=>$s->getEndDate()->format('Y-m-d H:i:s'),'location'=>$s->getLocation(),'instructor'=>$s->getInstructor(),'max_participants'=>$s->getMaxParticipants(),'status'=>$s->getStatus()->value]); }
    public function update(Request $r, string $id): JsonResponse { $this->updateHandler->handle(new UpdateSessionCommand($id,$r->input('code'),$r->input('name'),$r->input('start_date'),$r->input('end_date'),$r->input('location'),$r->input('instructor'),$r->integer('max_participants') ?: null)); return response()->json(['message'=>'Updated']); }
}
