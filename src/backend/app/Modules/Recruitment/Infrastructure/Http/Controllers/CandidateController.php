<?php

namespace App\Modules\Recruitment\Infrastructure\Http\Controllers;

use App\Modules\Recruitment\Application\CommandHandlers\AddCandidateHandler;
use App\Modules\Recruitment\Application\CommandHandlers\UpdateCandidateStageHandler;
use App\Modules\Recruitment\Application\Commands\AddCandidateCommand;
use App\Modules\Recruitment\Application\Commands\UpdateCandidateStageCommand;
use App\Modules\Recruitment\Domain\Repositories\CandidateRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CandidateController
{
    public function __construct(private CandidateRepositoryInterface $repo, private AddCandidateHandler $addHandler, private UpdateCandidateStageHandler $stageHandler) {}

    public function index(): JsonResponse
    {
        return response()->json(['data' => $this->repo->list()]);
    }

    public function store(Request $r): JsonResponse
    {
        $d = $r->validate(['requisition_id' => 'nullable|string', 'full_name' => 'required|string', 'email' => 'nullable|email', 'phone' => 'nullable|string', 'source' => 'required|string', 'cv_file_descriptor' => 'nullable|string', 'notes' => 'nullable|string']);
        $c = $this->addHandler->handle(new AddCandidateCommand($d['requisition_id'] ?? null, $d['full_name'], $d['email'] ?? null, $d['phone'] ?? null, $d['source'], $d['cv_file_descriptor'] ?? null, $d['notes'] ?? null));

        return response()->json(['data' => ['id' => (string) $c->getId()]], 201);
    }

    public function updateStage(Request $r, string $id): JsonResponse
    {
        $d = $r->validate(['status' => 'required|string']);
        $this->stageHandler->handle(new UpdateCandidateStageCommand($id, $d['status']));

        return response()->json(['data' => ['message' => 'OK']]);
    }
}
