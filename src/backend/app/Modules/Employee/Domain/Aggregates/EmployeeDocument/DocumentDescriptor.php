<?php

namespace App\Modules\Employee\Domain\Aggregates\EmployeeDocument;

use InvalidArgumentException;

final readonly class DocumentDescriptor
{
    public function __construct(
        public string $path,
        public string $originalName,
        public string $mime,
        public int $size,
    ) {
        if ($path === '') {
            throw new InvalidArgumentException('Document path cannot be empty.');
        }
    }
}
