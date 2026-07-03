<?php
namespace App\Modules\Training\Application\QueryHandlers;
use App\Modules\Training\Application\Queries\ListSessionsQuery; use App\Modules\Training\Domain\Repositories\TrainingSessionRepositoryInterface;
class ListSessionsHandler { public function __construct(private readonly TrainingSessionRepositoryInterface $repo) {} public function handle(ListSessionsQuery $q): array { return $this->repo->findByCourseId($q->courseId); } }
