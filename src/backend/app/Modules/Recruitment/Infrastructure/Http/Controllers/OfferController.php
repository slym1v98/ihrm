<?php

namespace App\Modules\Recruitment\Infrastructure\Http\Controllers;

use App\Modules\Recruitment\Application\CommandHandlers\AcceptOfferHandler;
use App\Modules\Recruitment\Application\CommandHandlers\ConvertCandidateToEmployeeHandler;
use App\Modules\Recruitment\Application\CommandHandlers\CreateOfferHandler;
use App\Modules\Recruitment\Application\CommandHandlers\RejectOfferHandler;
use App\Modules\Recruitment\Application\Commands\AcceptOfferCommand;
use App\Modules\Recruitment\Application\Commands\ConvertCandidateToEmployeeCommand;
use App\Modules\Recruitment\Application\Commands\CreateOfferCommand;
use App\Modules\Recruitment\Application\Commands\RejectOfferCommand;
use App\Modules\Recruitment\Domain\Repositories\OfferRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfferController
{
    public function __construct(private OfferRepositoryInterface $repo, private CreateOfferHandler $createHandler, private AcceptOfferHandler $acceptHandler, private RejectOfferHandler $rejectHandler, private ConvertCandidateToEmployeeHandler $convertHandler) {}

    public function index(): JsonResponse
    {
        return response()->json(['data' => $this->repo->list()]);
    }

    public function store(Request $r): JsonResponse
    {
        $d = $r->validate(['candidate_id' => 'required|string', 'requisition_id' => 'required|string', 'terms' => 'required|array', 'created_by' => 'required|string']);
        $o = $this->createHandler->handle(new CreateOfferCommand($d['candidate_id'], $d['requisition_id'], $d['terms'], $d['created_by']));

        return response()->json(['data' => ['id' => (string) $o->getId()]], 201);
    }

    public function accept(Request $r, string $id): JsonResponse
    {
        $this->acceptHandler->handle(new AcceptOfferCommand($id));

        return response()->json(['data' => ['message' => 'Accepted']]);
    }

    public function reject(Request $r, string $id): JsonResponse
    {
        $this->rejectHandler->handle(new RejectOfferCommand($id));

        return response()->json(['data' => ['message' => 'Rejected']]);
    }

    public function convert(Request $r, string $id): JsonResponse
    {
        try {
            $empId = $this->convertHandler->handle(new ConvertCandidateToEmployeeCommand($id));

            return response()->json(['data' => ['employee_id' => $empId]]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
