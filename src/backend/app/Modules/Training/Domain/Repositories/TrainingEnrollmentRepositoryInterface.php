<?php
namespace App\Modules\Training\Domain\Repositories;
use App\Modules\Training\Domain\Aggregates\TrainingEnrollment\TrainingEnrollment;
use App\Modules\Training\Domain\Aggregates\TrainingEnrollment\TrainingEnrollmentId;
interface TrainingEnrollmentRepositoryInterface {
    public function findById(TrainingEnrollmentId $id): ?TrainingEnrollment;
    public function findBySessionId(string $sessionId): array;
    public function findByEmployeeAndSession(string $sessionId, string $employeeId): ?TrainingEnrollment;
    public function save(TrainingEnrollment $enrollment): void;
}
