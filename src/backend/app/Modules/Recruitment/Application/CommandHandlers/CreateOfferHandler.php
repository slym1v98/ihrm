<?php

namespace App\Modules\Recruitment\Application\CommandHandlers;

use App\Modules\Recruitment\Application\Commands\CreateOfferCommand;
use App\Modules\Recruitment\Domain\Aggregates\Offer\Offer;
use App\Modules\Recruitment\Domain\Aggregates\Offer\OfferId;
use App\Modules\Recruitment\Domain\Repositories\OfferRepositoryInterface;

class CreateOfferHandler
{
    public function __construct(private OfferRepositoryInterface $repo) {}

    public function handle(CreateOfferCommand $cmd): Offer
    {
        $o = Offer::create(OfferId::generate(), $cmd->candidateId, $cmd->requisitionId, $cmd->terms, $cmd->createdBy);
        $this->repo->save($o);

        return $o;
    }
}
