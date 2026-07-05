<?php

namespace App\Modules\Recruitment\Domain\Repositories;

use App\Modules\Recruitment\Domain\Aggregates\Offer\Offer;
use App\Modules\Recruitment\Domain\Aggregates\Offer\OfferId;

interface OfferRepositoryInterface
{
    public function findById(OfferId $id): ?Offer;

    public function findByCandidateId(string $candidateId): ?Offer;

    /** @return Offer[] */
    public function list(): array;

    public function save(Offer $o): void;
}
