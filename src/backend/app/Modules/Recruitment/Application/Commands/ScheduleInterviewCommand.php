<?php namespace App\Modules\Recruitment\Application\Commands;
readonly class ScheduleInterviewCommand { public function __construct(public string $candidateId, public string $requisitionId, public array $interviewers, public string $scheduledAt, public ?string $notes = null) {} }
