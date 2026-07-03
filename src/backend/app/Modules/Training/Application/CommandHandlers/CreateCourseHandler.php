<?php
namespace App\Modules\Training\Application\CommandHandlers;
use App\Modules\Training\Application\Commands\CreateCourseCommand; use App\Modules\Training\Domain\Aggregates\TrainingCourse\{TrainingCourse,TrainingCourseId}; use App\Modules\Training\Domain\Repositories\TrainingCourseRepositoryInterface;
class CreateCourseHandler { public function __construct(private readonly TrainingCourseRepositoryInterface $repo) {} public function handle(CreateCourseCommand $cmd): TrainingCourse { $c=TrainingCourse::create(TrainingCourseId::generate(),$cmd->code,$cmd->name,$cmd->description,$cmd->category,$cmd->defaultDurationHours,$cmd->maxParticipants); $this->repo->save($c); return $c; } }
