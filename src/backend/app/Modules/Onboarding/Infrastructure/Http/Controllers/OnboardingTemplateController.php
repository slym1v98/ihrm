<?php

namespace App\Modules\Onboarding\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Onboarding\Application\Commands\CreateOnboardingTemplateCommand;
use App\Modules\Onboarding\Application\Commands\UpdateOnboardingTemplateCommand;
use App\Modules\Onboarding\Application\CommandHandlers\CreateOnboardingTemplateHandler;
use App\Modules\Onboarding\Application\CommandHandlers\UpdateOnboardingTemplateHandler;
use App\Modules\Onboarding\Application\Queries\ListTemplatesQuery;
use App\Modules\Onboarding\Application\QueryHandlers\ListTemplatesHandler;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingTemplate\OnboardingTemplateId;
use App\Modules\Onboarding\Domain\Repositories\OnboardingTemplateRepositoryInterface;
use App\Modules\Onboarding\Domain\Exceptions\OnboardingTemplateNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnboardingTemplateController extends Controller
{
    public function __construct(
        private readonly CreateOnboardingTemplateHandler $createHandler,
        private readonly UpdateOnboardingTemplateHandler $updateHandler,
        private readonly ListTemplatesHandler $listHandler,
        private readonly OnboardingTemplateRepositoryInterface $templateRepo,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = new ListTemplatesQuery(
            $request->input('department_id'),
            $request->input('position_id'),
            $request->input('location_id'),
            $request->input('employment_type'),
        );
        $templates = $this->listHandler->handle($query);
        $data = array_map(fn($t) => [
            'id' => $t->getId()->value,
            'code' => $t->getCode(),
            'name' => $t->getName(),
            'active' => $t->isActive(),
        ], $templates);
        return response()->json(['data' => $data]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:onboarding_templates,code',
            'name' => 'required|string|max:255',
            'rules' => 'required|array',
        ]);

        $command = new CreateOnboardingTemplateCommand(
            code: $request->input('code'),
            name: $request->input('name'),
            rules: $request->input('rules', []),
        );
        $template = $this->createHandler->handle($command);
        return response()->json(['data' => [
            'id' => $template->getId()->value,
            'code' => $template->getCode(),
            'name' => $template->getName(),
            'active' => $template->isActive(),
        ]], 201);
    }

    public function show(string $id): JsonResponse
    {
        $templateId = OnboardingTemplateId::fromString($id);
        $template = $this->templateRepo->findById($templateId);
        if (!$template) { throw new OnboardingTemplateNotFoundException($id); }
        return response()->json(['data' => [
            'id' => $template->getId()->value,
            'code' => $template->getCode(),
            'name' => $template->getName(),
            'rules' => $template->getRules()->toArray(),
            'active' => $template->isActive(),
        ]]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'rules' => 'required|array',
        ]);

        $command = new UpdateOnboardingTemplateCommand(
            id: $id, code: $request->input('code'),
            name: $request->input('name'), rules: $request->input('rules', []),
        );
        $this->updateHandler->handle($command);
        return response()->json(['message' => 'Updated']);
    }

    public function destroy(string $id): JsonResponse
    {
        $templateId = OnboardingTemplateId::fromString($id);
        $template = $this->templateRepo->findById($templateId);
        if (!$template) { throw new OnboardingTemplateNotFoundException($id); }
        $template->disable();
        $this->templateRepo->save($template);
        return response()->json(null, 204);
    }
}
