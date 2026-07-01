<?php

namespace App\Modules\Employee\Infrastructure\Http\Controllers;

use App\Modules\Employee\Application\CommandHandlers\EmployeeDocument\ArchiveEmployeeDocumentHandler;
use App\Modules\Employee\Application\CommandHandlers\EmployeeDocument\ReplaceEmployeeDocumentHandler;
use App\Modules\Employee\Application\CommandHandlers\EmployeeDocument\UploadEmployeeDocumentHandler;
use App\Modules\Employee\Application\Commands\EmployeeDocument\ArchiveEmployeeDocumentCommand;
use App\Modules\Employee\Application\Commands\EmployeeDocument\ReplaceEmployeeDocumentCommand;
use App\Modules\Employee\Application\Commands\EmployeeDocument\UploadEmployeeDocumentCommand;
use App\Modules\Employee\Domain\Aggregates\Employee\EmployeeId;
use App\Modules\Employee\Domain\Aggregates\EmployeeDocument\EmployeeDocumentId;
use App\Modules\Employee\Infrastructure\Http\Resources\EmployeeDocumentResource;
use App\Modules\Employee\Infrastructure\Persistence\Eloquent\EmployeeDocumentModel;
use App\Modules\Employee\Infrastructure\Storage\EmployeeDocumentStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeDocumentController
{
    public function __construct(
        private UploadEmployeeDocumentHandler $uploadHandler,
        private ReplaceEmployeeDocumentHandler $replaceHandler,
        private ArchiveEmployeeDocumentHandler $archiveHandler,
        private EmployeeDocumentStorage $storage,
    ) {}

    public function index(Request $request, string $employeeId): JsonResponse
    {
        $paginator = EmployeeDocumentModel::where('employee_id', $employeeId)
            ->orderBy('created_at', 'desc')
            ->paginate((int) $request->input('per_page', 20), ['*'], 'page', (int) $request->input('page', 1));

        return response()->json(new \App\Modules\Shared\Http\Resources\PaginatedCollection(
            $paginator->through(fn ($m) => new EmployeeDocumentResource($m))
        ));
    }

    public function store(Request $request, string $employeeId): JsonResponse
    {
        $file = $request->file('file');
        abort_if(!$file, 422, 'File required');

        $descriptor = $this->storage->store(EmployeeId::fromString($employeeId), EmployeeDocumentId::generate(), $file);

        $document = $this->uploadHandler->handle(
            new UploadEmployeeDocumentCommand($employeeId, $request->input('document_type'), $descriptor->path, $descriptor->originalName, $descriptor->mime, $descriptor->size, $request->input('category')),
            (string) $request->user()->id,
        );

        $model = EmployeeDocumentModel::find($document->id()->value);
        return response()->json(['data' => new EmployeeDocumentResource($model)], 201);
    }

    public function replace(Request $request, string $id): JsonResponse
    {
        $file = $request->file('file');
        abort_if(!$file, 422, 'File required');

        $current = EmployeeDocumentModel::findOrFail($id);
        $descriptor = $this->storage->store(
            EmployeeId::fromString($current->employee_id),
            EmployeeDocumentId::generate(),
            $file,
        );

        $this->replaceHandler->handle(
            new ReplaceEmployeeDocumentCommand($id, $descriptor->path, $descriptor->originalName, $descriptor->mime, $descriptor->size),
            (string) $request->user()->id,
        );

        return response()->json(['data' => new EmployeeDocumentResource(EmployeeDocumentModel::find($id))]);
    }

    public function archive(Request $request, string $id): JsonResponse
    {
        $this->archiveHandler->handle(
            new ArchiveEmployeeDocumentCommand($id),
            (string) $request->user()->id,
        );

        return response()->json(['data' => new EmployeeDocumentResource(EmployeeDocumentModel::find($id))]);
    }

    public function download(string $id): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $model = EmployeeDocumentModel::findOrFail($id);
        return \Illuminate\Support\Facades\Storage::disk('minio')->download($model->file_path, $model->file_original_name);
    }
}
