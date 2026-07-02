<?php
namespace App\Modules\Recruitment\Application\CommandHandlers;
use App\Modules\Recruitment\Application\Commands\AcceptOfferCommand;
use App\Modules\Recruitment\Domain\Aggregates\Offer\OfferId;
use App\Modules\Recruitment\Domain\Exceptions\OfferNotFoundException;
use App\Modules\Recruitment\Domain\Repositories\{OfferRepositoryInterface,CandidateRepositoryInterface};
use App\Modules\Recruitment\Domain\Aggregates\Candidate\CandidateId;
use App\Modules\Recruitment\Domain\ValueObjects\CandidateStatus;
use Carbon\CarbonImmutable;
class AcceptOfferHandler {
    public function __construct(private OfferRepositoryInterface $offerRepo, private CandidateRepositoryInterface $candidateRepo) {}
    public function handle(AcceptOfferCommand $cmd): void {
        $o=$this->offerRepo->findById(new OfferId($cmd->offerId));
        if(!$o) throw new OfferNotFoundException($cmd->offerId);
        $o->accept(CarbonImmutable::now()); $this->offerRepo->save($o);
        $c=$this->candidateRepo->findById(new CandidateId($o->getCandidateId()));
        if($c && $c->getStatus()->canTransitionTo(CandidateStatus::Hired)) { $c->moveTo(CandidateStatus::Hired); $this->candidateRepo->save($c); }
    }
}
