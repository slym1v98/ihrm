<?php namespace App\Modules\Recruitment\Application\Commands;
readonly class UpdateRequisitionCommand { public function __construct(public string $id, public ?string $position=null, public ?int $headcount=null, public ?string $reason=null) {} }
