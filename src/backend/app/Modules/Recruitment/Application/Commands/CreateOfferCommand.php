<?php

namespace App\Modules\Recruitment\Application\Commands;

readonly class CreateOfferCommand
{
    public function __construct(public string $candidateId, public string $requisitionId, public array $terms, public string $createdBy) {}
}
