<?php

namespace App\Modules\Workflow\Application\Contracts;

interface SubjectDataProvider
{
    public function subjectType(): string;

    public function fetchContext(string $subjectId): array;
}
