<?php namespace App\Modules\Recruitment\Application\Commands;
readonly class AddCandidateCommand { public function __construct(public ?string $requisitionId, public string $fullName, public ?string $email, public ?string $phone, public string $source, public ?string $cvFileDescriptor = null, public ?string $notes = null) {} }
