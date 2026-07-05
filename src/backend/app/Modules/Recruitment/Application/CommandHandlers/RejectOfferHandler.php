<?php

namespace App\Modules\Recruitment\Application\CommandHandlers;

use App\Modules\Recruitment\Application\Commands\RejectOfferCommand;
use App\Modules\Recruitment\Domain\Aggregates\Offer\OfferId;
use App\Modules\Recruitment\Domain\Exceptions\OfferNotFoundException;
use App\Modules\Recruitment\Domain\Repositories\OfferRepositoryInterface;
use Carbon\CarbonImmutable;

class RejectOfferHandler
{
    public function __construct(private OfferRepositoryInterface $repo) {}

    public function handle(RejectOfferCommand $cmd): void
    {
        $o = $this->repo->findById(new OfferId($cmd->offerId));
        if (! $o) {
            throw new OfferNotFoundException($cmd->offerId);
        }
        $o->reject(CarbonImmutable::now());
        $this->repo->save($o);
    }
}
