<?php

namespace App\Modules\Training\Domain\Repositories;

use App\Modules\Training\Domain\Aggregates\TrainingCourse\TrainingCourse;
use App\Modules\Training\Domain\Aggregates\TrainingCourse\TrainingCourseId;

interface TrainingCourseRepositoryInterface
{
    public function findById(TrainingCourseId $id): ?TrainingCourse;

    public function findByCode(string $code): ?TrainingCourse;

    public function all(): array;

    public function save(TrainingCourse $course): void;
}
