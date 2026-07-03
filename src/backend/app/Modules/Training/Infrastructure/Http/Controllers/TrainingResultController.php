<?php
namespace App\Modules\Training\Infrastructure\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Modules\Training\Application\Commands\RecordResultCommand; use App\Modules\Training\Application\CommandHandlers\RecordResultHandler;
use App\Modules\Training\Domain\Aggregates\TrainingResult\TrainingResultId; use App\Modules\Training\Domain\Repositories\TrainingResultRepositoryInterface; use App\Modules\Training\Domain\Exceptions\TrainingResultNotFoundException;
use Illuminate\Http\JsonResponse; use Illuminate\Http\Request;
class TrainingResultController extends Controller {
    public function __construct(private readonly RecordResultHandler $recordHandler, private readonly TrainingResultRepositoryInterface $resultRepo) {}
    public function store(Request $r, string $id): JsonResponse { try{$t=$this->recordHandler->handle(new RecordResultCommand($id,$r->float('score') ?: null,$r->boolean('passed'),$r->input('certificate_code'),$r->input('notes'))); return response()->json(['id'=>$t->getId()->value],201);}catch(\Exception $e){return response()->json(['error'=>$e->getMessage()],422);} }
    public function show(string $id): JsonResponse { $t=$this->resultRepo->findById(TrainingResultId::fromString($id)) ?? throw new TrainingResultNotFoundException($id); return response()->json(['id'=>$t->getId()->value,'enrollment_id'=>$t->getEnrollmentId(),'score'=>$t->getScore(),'passed'=>$t->getPassed(),'certificate_code'=>$t->getCertificateCode(),'issued_at'=>$t->getIssuedAt()?->format('Y-m-d H:i:s'),'notes'=>$t->getNotes()]); }
}
