<?php
namespace App\Modules\Training\Domain\Repositories;
use App\Modules\Training\Domain\Aggregates\TrainingSession\TrainingSession;
use App\Modules\Training\Domain\Aggregates\TrainingSession\TrainingSessionId;
interface TrainingSessionRepositoryInterface {
    public function findById(TrainingSessionId $id): ?TrainingSession;
    public function findByCourseId(string $courseId): array;
    public function countEnrolled(string $sessionId): int;
    public function save(TrainingSession $session): void;
}
