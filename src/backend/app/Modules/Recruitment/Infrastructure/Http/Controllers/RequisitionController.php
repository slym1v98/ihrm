<?php
namespace App\Modules\Recruitment\Infrastructure\Http\Controllers;
use App\Modules\Recruitment\Application\CommandHandlers\{CreateRequisitionHandler, UpdateRequisitionHandler, SubmitRequisitionHandler};
use App\Modules\Recruitment\Application\Commands\{CreateRequisitionCommand, UpdateRequisitionCommand, SubmitRequisitionCommand};
use App\Modules\Recruitment\Domain\Repositories\RecruitmentRequisitionRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
class RequisitionController {
    public function __construct(private RecruitmentRequisitionRepositoryInterface $repo, private CreateRequisitionHandler $createHandler, private SubmitRequisitionHandler $submitHandler) {}
    public function index(): JsonResponse { return response()->json(['data'=>$this->repo->list()]); }
    public function store(Request $r): JsonResponse {
        $d=$r->validate(['department_id'=>'required|string','position'=>'required|string','headcount'=>'required|integer|min:1','reason'=>'required|string']);
        $req=$this->createHandler->handle(new CreateRequisitionCommand($d['department_id'],$d['position'],$d['headcount'],$d['reason'],$r->user()->id));
        return response()->json(['data'=>['id'=>(string)$req->getId()]],201);
    }
    public function submit(Request $r, string $id): JsonResponse {
        $this->submitHandler->handle(new SubmitRequisitionCommand($id,$r->user()->id));
        return response()->json(['data'=>['message'=>'Submitted']]);
    }
}
