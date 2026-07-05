<?php

namespace App\Modules\Training\Application\Queries;

class ListSessionsQuery
{
    public function __construct(public readonly string $courseId) {}
}
