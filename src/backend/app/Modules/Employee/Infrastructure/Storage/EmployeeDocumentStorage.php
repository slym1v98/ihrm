<?php

namespace App\Modules\Employee\Infrastructure\Storage;

use App\Modules\Employee\Domain\Aggregates\Employee\EmployeeId;
use App\Modules\Employee\Domain\Aggregates\EmployeeDocument\DocumentDescriptor;
use App\Modules\Employee\Domain\Aggregates\EmployeeDocument\EmployeeDocumentId;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmployeeDocumentStorage
{
    public function store(EmployeeId $employeeId, EmployeeDocumentId $documentId, UploadedFile $file): DocumentDescriptor
    {
        $name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $extension = $file->getClientOriginalExtension();
        $fileName = $documentId->value . '_' . $name . ($extension ? ".{$extension}" : '');
        $path = "employees/{$employeeId->value}/documents/{$fileName}";

        Storage::disk('minio')->put($path, file_get_contents($file->getRealPath()));

        return new DocumentDescriptor($path, $file->getClientOriginalName(), $file->getMimeType() ?: 'application/octet-stream', $file->getSize());
    }

    public function delete(string $path): void
    {
        Storage::disk('minio')->delete($path);
    }
}
