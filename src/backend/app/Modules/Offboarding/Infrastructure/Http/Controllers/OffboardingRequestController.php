<?php

namespace App\Modules\Offboarding\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Offboarding\Application\CommandHandlers\ApproveOffboardingRequestHandler;
use App\Modules\Offboarding\Application\CommandHandlers\CreateOffboardingRequestHandler;
use App\Modules\Offboarding\Application\CommandHandlers\RejectOffboardingRequestHandler;
use App\Modules\Offboarding\Application\CommandHandlers\SubmitOffboardingRequestHandler;
use App\Modules\Offboarding\Application\Commands\ApproveOffboardingRequestCommand;
use App\Modules\Offboarding\Application\Commands\CreateOffboardingRequestCommand;
use App\Modules\Offboarding\Application\Commands\RejectOffboardingRequestCommand;
use App\Modules\Offboarding\Application\Commands\SubmitOffboardingRequestCommand;
use App\Modules\Offboarding\Application\Queries\ListRequestsQuery;
use App\Modules\Offboarding\Application\QueryHandlers\ListRequestsHandler;
use App\Modules\Offboarding\Domain\Aggregates\OffboardingRequest\OffboardingRequestId;
use App\Modules\Offboarding\Domain\Exceptions\OffboardingRequestNotFoundException;
use App\Modules\Offboarding\Domain\Repositories\OffboardingRequestRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OffboardingRequestController extends Controller
{
    public function __construct(
        private readonly CreateOffboardingRequestHandler $createHandler,
        private readonly SubmitOffboardingRequestHandler $submitHandler,
        private readonly ApproveOffboardingRequestHandler $approveHandler,
        private readonly RejectOffboardingRequestHandler $rejectHandler,
        private readonly ListRequestsHandler $listHandler,
        private readonly OffboardingRequestRepositoryInterface $requestRepo,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = new ListRequestsQuery($request->input('employee_id'));
        $items = $this->listHandler->handle($query);
        $data = array_map(fn ($r) => ['id' => $r->getId()->value, 'employee_id' => $r->getEmployeeId(), 'type' => $r->getType()->value, 'status' => $r->getStatus()->value], $items);

        return response()->json(['data' => $data]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate(['employee_id' => 'required|uuid', 'type' => 'required|in:resignation,company_initiated', 'reason' => 'required|string', 'requested_last_working_date' => 'required|date']);
        $cmd = new CreateOffboardingRequestCommand(
            $request->input('employee_id'), $request->input('type'), $request->input('reason'), $request->input('requested_last_working_date'));
        $r = $this->createHandler->handle($cmd);

        return response()->json(['data' => ['id' => $r->getId()->value, 'status' => $r->getStatus()->value]], 201);
    }

    public function show(string $id): JsonResponse
    {
        $r = $this->requestRepo->findById(OffboardingRequestId::fromString($id));
        if (! $r) {
            throw new OffboardingRequestNotFoundException($id);
        }

        return response()->json(['data' => ['id' => $r->getId()->value, 'status' => $r->getStatus()->value]]);
    }

    public function submit(string $id): JsonResponse
    {
        try {
            $this->submitHandler->handle(new SubmitOffboardingRequestCommand($id));

            return response()->json(['message' => 'Submitted']);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function approve(Request $request, string $id): JsonResponse
    {
        try {
            $cmd = new ApproveOffboardingRequestCommand($id, $request->input('approved_last_working_date', date('Y-m-d')));
            $this->approveHandler->handle($cmd);

            return response()->json(['message' => 'Approved']);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function reject(Request $request, string $id): JsonResponse
    {
        try {
            $this->rejectHandler->handle(new RejectOffboardingRequestCommand($id, $request->input('reason')));

            return response()->json(['message' => 'Rejected']);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
