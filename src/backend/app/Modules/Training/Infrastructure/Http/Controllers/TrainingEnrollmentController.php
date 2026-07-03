<?php
namespace App\Modules\Training\Infrastructure\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Modules\Training\Application\Commands\{EnrollEmployeeCommand,CancelEnrollmentCommand,RecordAttendanceCommand,CompleteEnrollmentCommand};
use App\Modules\Training\Application\CommandHandlers\{EnrollEmployeeHandler,CancelEnrollmentHandler,RecordAttendanceHandler,CompleteEnrollmentHandler};
use App\Modules\Training\Application\Queries\ListEnrollmentsQuery; use App\Modules\Training\Application\QueryHandlers\ListEnrollmentsHandler;
use App\Modules\Training\Domain\Aggregates\TrainingEnrollment\TrainingEnrollmentId; use App\Modules\Training\Domain\Repositories\TrainingEnrollmentRepositoryInterface; use App\Modules\Training\Domain\Exceptions\TrainingEnrollmentNotFoundException;
use Illuminate\Http\JsonResponse; use Illuminate\Http\Request;
class TrainingEnrollmentController extends Controller {
    public function __construct(private readonly EnrollEmployeeHandler $enrollHandler, private readonly CancelEnrollmentHandler $cancelHandler, private readonly RecordAttendanceHandler $attendanceHandler, private readonly CompleteEnrollmentHandler $completeHandler, private readonly ListEnrollmentsHandler $listHandler, private readonly TrainingEnrollmentRepositoryInterface $enrollmentRepo) {}
    public function index(string $sessionId): JsonResponse { $items=$this->listHandler->handle(new ListEnrollmentsQuery($sessionId)); return response()->json(array_map(fn($e)=>['id'=>$e->getId()->value,'session_id'=>$e->getSessionId(),'employee_id'=>$e->getEmployeeId(),'enrolled_at'=>$e->getEnrolledAt()->format('Y-m-d H:i:s'),'attendance'=>$e->getAttendance(),'status'=>$e->getStatus()->value],$items)); }
    public function store(Request $r, string $sessionId): JsonResponse { try{$e=$this->enrollHandler->handle(new EnrollEmployeeCommand($sessionId,$r->input('employee_id'))); return response()->json(['id'=>$e->getId()->value],201);}catch(\Exception $ex){return response()->json(['error'=>$ex->getMessage()],422);} }
    public function cancel(string $id): JsonResponse { try{$this->cancelHandler->handle(new CancelEnrollmentCommand($id)); return response()->json(['message'=>'Cancelled']);}catch(\Exception $e){return response()->json(['error'=>$e->getMessage()],422);} }
    public function attendance(Request $r, string $id): JsonResponse { try{$this->attendanceHandler->handle(new RecordAttendanceCommand($id,$r->input('attendance',[]))); return response()->json(['message'=>'Attendance recorded']);}catch(\Exception $e){return response()->json(['error'=>$e->getMessage()],422);} }
    public function complete(string $id): JsonResponse { try{$this->completeHandler->handle(new CompleteEnrollmentCommand($id)); return response()->json(['message'=>'Completed']);}catch(\Exception $e){return response()->json(['error'=>$e->getMessage()],422);} }
}
