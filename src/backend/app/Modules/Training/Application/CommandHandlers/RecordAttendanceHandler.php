<?php
namespace App\Modules\Training\Application\CommandHandlers;
use App\Modules\Training\Application\Commands\RecordAttendanceCommand; use App\Modules\Training\Domain\Aggregates\TrainingEnrollment\TrainingEnrollmentId; use App\Modules\Training\Domain\Repositories\TrainingEnrollmentRepositoryInterface; use App\Modules\Training\Domain\Exceptions\TrainingEnrollmentNotFoundException;
class RecordAttendanceHandler { public function __construct(private readonly TrainingEnrollmentRepositoryInterface $repo) {} public function handle(RecordAttendanceCommand $cmd): void { $e=$this->repo->findById(TrainingEnrollmentId::fromString($cmd->id)) ?? throw new TrainingEnrollmentNotFoundException($cmd->id); $e->recordAttendance($cmd->attendance); $this->repo->save($e); } }
