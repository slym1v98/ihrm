<?php
namespace App\Modules\Training\Application\QueryHandlers;
use App\Modules\Training\Application\Queries\ListEnrollmentsQuery; use App\Modules\Training\Domain\Repositories\TrainingEnrollmentRepositoryInterface;
class ListEnrollmentsHandler { public function __construct(private readonly TrainingEnrollmentRepositoryInterface $repo) {} public function handle(ListEnrollmentsQuery $q): array { return $this->repo->findBySessionId($q->sessionId); } }
