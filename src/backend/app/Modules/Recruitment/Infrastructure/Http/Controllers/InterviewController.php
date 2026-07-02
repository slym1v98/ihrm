<?php
namespace App\Modules\Recruitment\Infrastructure\Http\Controllers;
use App\Modules\Recruitment\Application\CommandHandlers\{ScheduleInterviewHandler, SubmitScorecardHandler};
use App\Modules\Recruitment\Application\Commands\{ScheduleInterviewCommand, SubmitScorecardCommand};
use App\Modules\Recruitment\Domain\Repositories\InterviewRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
class InterviewController {
    public function __construct(private InterviewRepositoryInterface $repo, private ScheduleInterviewHandler $scheduleHandler, private SubmitScorecardHandler $scorecardHandler) {}
    public function index(): JsonResponse { return response()->json(['data'=>$this->repo->list()]); }
    public function store(Request $r): JsonResponse {
        $d=$r->validate(['candidate_id'=>'required|string','requisition_id'=>'required|string','interviewers'=>'required|array','interviewers.*'=>'string','scheduled_at'=>'required|date','notes'=>'nullable|string']);
        $i=$this->scheduleHandler->handle(new ScheduleInterviewCommand($d['candidate_id'],$d['requisition_id'],$d['interviewers'],$d['scheduled_at'],$d['notes']??null));
        return response()->json(['data'=>['id'=>(string)$i->getId()]],201);
    }
    public function submitScorecard(Request $r, string $id): JsonResponse {
        $d=$r->validate(['interviewer_id'=>'required|string','score'=>'required|integer|min:1|max:10','comment'=>'required|string']);
        $this->scorecardHandler->handle(new SubmitScorecardCommand($id,$d['interviewer_id'],$d['score'],$d['comment']));
        return response()->json(['data'=>['message'=>'OK']]);
    }
}
