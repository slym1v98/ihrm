<?php

namespace App\Modules\Training\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Training\Application\CommandHandlers\CreateCourseHandler;
use App\Modules\Training\Application\CommandHandlers\DeactivateCourseHandler;
use App\Modules\Training\Application\CommandHandlers\UpdateCourseHandler;
use App\Modules\Training\Application\Commands\CreateCourseCommand;
use App\Modules\Training\Application\Commands\DeactivateCourseCommand;
use App\Modules\Training\Application\Commands\UpdateCourseCommand;
use App\Modules\Training\Application\Queries\ListCoursesQuery;
use App\Modules\Training\Application\QueryHandlers\ListCoursesHandler;
use App\Modules\Training\Domain\Aggregates\TrainingCourse\TrainingCourse;
use App\Modules\Training\Domain\Aggregates\TrainingCourse\TrainingCourseId;
use App\Modules\Training\Domain\Exceptions\TrainingCourseNotFoundException;
use App\Modules\Training\Domain\Repositories\TrainingCourseRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrainingCourseController extends Controller
{
    public function __construct(
        private readonly CreateCourseHandler $createHandler,
        private readonly UpdateCourseHandler $updateHandler,
        private readonly DeactivateCourseHandler $deactivateHandler,
        private readonly ListCoursesHandler $listHandler,
        private readonly TrainingCourseRepositoryInterface $courseRepo,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $items = $this->listHandler->handle(new ListCoursesQuery($request->has('active') ? $request->boolean('active') : null));

        return response()->json(['data' => array_map(fn (TrainingCourse $course) => $this->toArray($course), $items)]);
    }

    public function store(Request $request): JsonResponse
    {
        $course = $this->createHandler->handle(new CreateCourseCommand(
            $request->input('code'),
            $request->input('name'),
            $request->input('description'),
            $request->input('category'),
            $request->integer('default_duration_hours') ?: null,
            $request->integer('max_participants') ?: null,
        ));

        return response()->json(['data' => $this->toArray($course)], 201);
    }

    public function show(string $id): JsonResponse
    {
        $course = $this->courseRepo->findById(TrainingCourseId::fromString($id)) ?? throw new TrainingCourseNotFoundException($id);

        return response()->json(['data' => $this->toArray($course)]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $this->updateHandler->handle(new UpdateCourseCommand(
            $id,
            $request->input('code'),
            $request->input('name'),
            $request->input('description'),
            $request->input('category'),
            $request->integer('default_duration_hours') ?: null,
            $request->integer('max_participants') ?: null,
        ));

        return response()->json(['data' => null]);
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $this->deactivateHandler->handle(new DeactivateCourseCommand($id));

            return response()->json(['data' => null]);
        } catch (\Exception $e) {
            return response()->json(['error' => ['message' => $e->getMessage()]], 422);
        }
    }

    private function toArray(TrainingCourse $course): array
    {
        return [
            'id' => $course->getId()->value,
            'code' => $course->getCode(),
            'name' => $course->getName(),
            'description' => $course->getDescription(),
            'category' => $course->getCategory(),
            'default_duration_hours' => $course->getDefaultDurationHours(),
            'max_participants' => $course->getMaxParticipants(),
            'active' => $course->isActive(),
        ];
    }
}
