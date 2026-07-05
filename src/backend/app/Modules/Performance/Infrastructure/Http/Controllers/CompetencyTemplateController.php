<?php

namespace App\Modules\Performance\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Performance\Application\CommandHandlers\CreateTemplateHandler;
use App\Modules\Performance\Application\CommandHandlers\UpdateTemplateHandler;
use App\Modules\Performance\Application\Commands\CreateTemplateCommand;
use App\Modules\Performance\Application\Commands\UpdateTemplateCommand;
use App\Modules\Performance\Domain\Aggregates\CompetencyTemplate\CompetencyTemplateId;
use App\Modules\Performance\Domain\Exceptions\CompetencyTemplateNotFoundException;
use App\Modules\Performance\Domain\Repositories\CompetencyTemplateRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompetencyTemplateController extends Controller
{
    public function __construct(
        private readonly CreateTemplateHandler $createHandler,
        private readonly UpdateTemplateHandler $updateHandler,
        private readonly CompetencyTemplateRepositoryInterface $templateRepo,
    ) {}

    public function index(): JsonResponse
    {
        $items = $this->templateRepo->all();
        $data = array_map(fn ($t) => ['id' => $t->getId()->value, 'code' => $t->getCode(), 'name' => $t->getName(), 'rules' => $t->getRules(), 'active' => $t->isActive()], $items);

        return response()->json($data);
    }

    public function store(Request $r): JsonResponse
    {
        $t = $this->createHandler->handle(new CreateTemplateCommand($r->input('code'), $r->input('name'), $r->input('rules', [])));

        return response()->json(['id' => $t->getId()->value], 201);
    }

    public function show(string $id): JsonResponse
    {
        $t = $this->templateRepo->findById(CompetencyTemplateId::fromString($id)) ?? throw new CompetencyTemplateNotFoundException($id);

        return response()->json(['id' => $t->getId()->value, 'code' => $t->getCode(), 'name' => $t->getName(), 'rules' => $t->getRules(), 'active' => $t->isActive()]);
    }

    public function update(Request $r, string $id): JsonResponse
    {
        try {
            $this->updateHandler->handle(new UpdateTemplateCommand($id, $r->input('code'), $r->input('name'), $r->input('rules', [])));

            return response()->json(['message' => 'Updated']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $this->templateRepo->delete(CompetencyTemplateId::fromString($id));

            return response()->json(['message' => 'Deleted']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
