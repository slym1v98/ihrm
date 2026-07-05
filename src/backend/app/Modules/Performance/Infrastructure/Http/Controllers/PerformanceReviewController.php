<?php

namespace App\Modules\Performance\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Performance\Application\CommandHandlers\CreateReviewHandler;
use App\Modules\Performance\Application\CommandHandlers\FinalizeReviewHandler;
use App\Modules\Performance\Application\CommandHandlers\SubmitHrReviewHandler;
use App\Modules\Performance\Application\CommandHandlers\SubmitManagerReviewHandler;
use App\Modules\Performance\Application\CommandHandlers\SubmitSelfReviewHandler;
use App\Modules\Performance\Application\Commands\CreateReviewCommand;
use App\Modules\Performance\Application\Commands\FinalizeReviewCommand;
use App\Modules\Performance\Application\Commands\SubmitHrReviewCommand;
use App\Modules\Performance\Application\Commands\SubmitManagerReviewCommand;
use App\Modules\Performance\Application\Commands\SubmitSelfReviewCommand;
use App\Modules\Performance\Application\Queries\ListReviewsQuery;
use App\Modules\Performance\Application\QueryHandlers\ListReviewsHandler;
use App\Modules\Performance\Domain\Aggregates\PerformanceReview\PerformanceReviewId;
use App\Modules\Performance\Domain\Exceptions\PerformanceReviewNotFoundException;
use App\Modules\Performance\Domain\Repositories\PerformanceReviewRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PerformanceReviewController extends Controller
{
    public function __construct(
        private readonly CreateReviewHandler $createHandler,
        private readonly SubmitSelfReviewHandler $selfHandler,
        private readonly SubmitManagerReviewHandler $managerHandler,
        private readonly SubmitHrReviewHandler $hrHandler,
        private readonly FinalizeReviewHandler $finalizeHandler,
        private readonly ListReviewsHandler $listHandler,
        private readonly PerformanceReviewRepositoryInterface $reviewRepo,
    ) {}

    public function index(Request $r): JsonResponse
    {
        $q = new ListReviewsQuery($r->input('cycle_id'));
        $items = $this->listHandler->handle($q);
        $data = array_map(fn ($rv) => [
            'id' => $rv->getId()->value, 'cycle_id' => $rv->getCycleId(), 'employee_id' => $rv->getEmployeeId(),
            'self_assessment' => $rv->getSelfAssessment(), 'manager_assessment' => $rv->getManagerAssessment(),
            'hr_assessment' => $rv->getHrAssessment(), 'final_score' => $rv->getFinalScore(),
            'status' => $rv->getStatus()->value, 'finalized_at' => $rv->getFinalizedAt()?->format('Y-m-d H:i:s'),
        ], $items);

        return response()->json(['data' => $data]);
    }

    public function store(Request $r): JsonResponse
    {
        $cmd = new CreateReviewCommand($r->input('cycle_id'), $r->input('employee_id'));
        $rv = $this->createHandler->handle($cmd);

        return response()->json(['data' => ['id' => $rv->getId()->value]], 201);
    }

    public function show(string $id): JsonResponse
    {
        $rv = $this->reviewRepo->findById(PerformanceReviewId::fromString($id)) ?? throw new PerformanceReviewNotFoundException($id);

        return response()->json(['data' => [
            'id' => $rv->getId()->value, 'cycle_id' => $rv->getCycleId(), 'employee_id' => $rv->getEmployeeId(),
            'self_assessment' => $rv->getSelfAssessment(), 'manager_assessment' => $rv->getManagerAssessment(),
            'hr_assessment' => $rv->getHrAssessment(), 'final_score' => $rv->getFinalScore(),
            'status' => $rv->getStatus()->value, 'finalized_at' => $rv->getFinalizedAt()?->format('Y-m-d H:i:s'),
        ]]);
    }

    public function submitSelf(Request $r, string $id): JsonResponse
    {
        try {
            $this->selfHandler->handle(new SubmitSelfReviewCommand($id, $r->input('assessment', [])));

            return response()->json(['message' => 'Self assessment submitted']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function submitManager(Request $r, string $id): JsonResponse
    {
        try {
            $this->managerHandler->handle(new SubmitManagerReviewCommand($id, $r->input('assessment', [])));

            return response()->json(['message' => 'Manager assessment submitted']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function submitHr(Request $r, string $id): JsonResponse
    {
        try {
            $this->hrHandler->handle(new SubmitHrReviewCommand($id, $r->input('assessment', [])));

            return response()->json(['message' => 'HR assessment submitted']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function finalize(Request $r, string $id): JsonResponse
    {
        try {
            $this->finalizeHandler->handle(new FinalizeReviewCommand($id, $r->input('final_score')));

            return response()->json(['message' => 'Review finalized']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
