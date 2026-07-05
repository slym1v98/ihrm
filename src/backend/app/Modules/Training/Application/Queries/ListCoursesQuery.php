<?php

namespace App\Modules\Training\Application\Queries;

class ListCoursesQuery
{
    public function __construct(public readonly ?bool $active = null) {}
}
