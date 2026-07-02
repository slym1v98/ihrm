<?php namespace App\Modules\Recruitment\Application\Commands;
readonly class SubmitScorecardCommand { public function __construct(public string $interviewId, public string $interviewerId, public int $score, public string $comment) {} }
