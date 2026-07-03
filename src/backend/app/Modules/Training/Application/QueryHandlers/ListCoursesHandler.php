<?php
namespace App\Modules\Training\Application\QueryHandlers;
use App\Modules\Training\Application\Queries\ListCoursesQuery; use App\Modules\Training\Domain\Repositories\TrainingCourseRepositoryInterface;
class ListCoursesHandler { public function __construct(private readonly TrainingCourseRepositoryInterface $repo) {} public function handle(ListCoursesQuery $q): array { $items=$this->repo->all(); if ($q->active!==null) $items=array_values(array_filter($items,fn($c)=>$c->isActive()===$q->active)); return $items; } }
