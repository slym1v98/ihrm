<?php

namespace App\Modules\Training\Application\CommandHandlers;

use App\Modules\Training\Application\Commands\EnrollEmployeeCommand;
use App\Modules\Training\Domain\Aggregates\TrainingEnrollment\TrainingEnrollment;
use App\Modules\Training\Domain\Aggregates\TrainingEnrollment\TrainingEnrollmentId;
use App\Modules\Training\Domain\Aggregates\TrainingSession\TrainingSessionId;
use App\Modules\Training\Domain\Exceptions\TrainingSessionNotFoundException;
use App\Modules\Training\Domain\Repositories\TrainingEnrollmentRepositoryInterface;
use App\Modules\Training\Domain\Repositories\TrainingSessionRepositoryInterface;

class EnrollEmployeeHandler
{
    public function __construct(private readonly TrainingEnrollmentRepositoryInterface $enrollments, private readonly TrainingSessionRepositoryInterface $sessions) {}

    public function handle(EnrollEmployeeCommand $cmd): TrainingEnrollment
    {
        $s = $this->sessions->findById(TrainingSessionId::fromString($cmd->sessionId)) ?? throw new TrainingSessionNotFoundException($cmd->sessionId);
        if ($this->enrollments->findByEmployeeAndSession($cmd->sessionId, $cmd->employeeId)) {
            throw new \RuntimeException('Employee already enrolled');
        } $s->assertCanEnroll($this->sessions->countEnrolled($cmd->sessionId));
        $e = TrainingEnrollment::enroll(TrainingEnrollmentId::generate(), $cmd->sessionId, $cmd->employeeId, new \DateTimeImmutable);
        $this->enrollments->save($e);

        return $e;
    }
}
