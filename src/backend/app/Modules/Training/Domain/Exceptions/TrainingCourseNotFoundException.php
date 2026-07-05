<?php

namespace App\Modules\Training\Domain\Exceptions;

class TrainingCourseNotFoundException extends \RuntimeException
{
    public function __construct(string $id)
    {
        parent::__construct("TrainingCourseNotFoundException: $id");
    }
}
