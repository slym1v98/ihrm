<?php
namespace App\Modules\Training\Application\CommandHandlers;
use App\Modules\Training\Application\Commands\DeactivateCourseCommand; use App\Modules\Training\Domain\Aggregates\TrainingCourse\TrainingCourseId; use App\Modules\Training\Domain\Repositories\TrainingCourseRepositoryInterface; use App\Modules\Training\Domain\Exceptions\TrainingCourseNotFoundException;
class DeactivateCourseHandler { public function __construct(private readonly TrainingCourseRepositoryInterface $repo) {} public function handle(DeactivateCourseCommand $cmd): void { $c=$this->repo->findById(TrainingCourseId::fromString($cmd->id)) ?? throw new TrainingCourseNotFoundException($cmd->id); $c->deactivate(); $this->repo->save($c); } }
