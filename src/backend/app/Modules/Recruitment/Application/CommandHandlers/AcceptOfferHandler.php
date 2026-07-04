<?php
namespace App\Modules\Recruitment\Application\CommandHandlers;
use App\Modules\Recruitment\Application\Commands\AcceptOfferCommand;
use App\Modules\Recruitment\Domain\Aggregates\Offer\OfferId;
use App\Modules\Recruitment\Domain\Exceptions\OfferNotFoundException;
use App\Modules\Recruitment\Domain\Repositories\{OfferRepositoryInterface,CandidateRepositoryInterface};
use App\Modules\Recruitment\Domain\Aggregates\Candidate\CandidateId;
use App\Modules\Recruitment\Domain\ValueObjects\CandidateStatus;
use App\Modules\Recruitment\Domain\Events\OfferAccepted;
use App\Modules\Recruitment\Domain\Events\CandidateHired;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Event;
class AcceptOfferHandler {
    public function __construct(private OfferRepositoryInterface $offerRepo, private CandidateRepositoryInterface $candidateRepo) {}
    public function handle(AcceptOfferCommand $cmd): void {
        $o=$this->offerRepo->findById(new OfferId($cmd->offerId));
        if(!$o) throw new OfferNotFoundException($cmd->offerId);
        $o->accept(CarbonImmutable::now()); $this->offerRepo->save($o);
        $c=$this->candidateRepo->findById(new CandidateId($o->getCandidateId()));
        if($c && $c->getStatus()->canTransitionTo(CandidateStatus::Hired)) {
            $c->moveTo(CandidateStatus::Hired);
            $this->candidateRepo->save($c);
        }

        Event::dispatch(new OfferAccepted([
            'offer_id' => $o->getId()->value(),
            'candidate_id' => $o->getCandidateId(),
            'requisition_id' => $o->getRequisitionId(),
            'accepted_at' => CarbonImmutable::now()->toIso8601String(),
        ]));

        if ($c) {
            Event::dispatch(new CandidateHired([
                'candidate_id' => $c->getId()->value(),
                'employee_id' => null,
                'full_name' => $c->getFullName(),
                'email' => $c->getEmail(),
                'department_id' => null,
                'position_id' => null,
                'start_date' => CarbonImmutable::now()->toDateString(),
            ]));
        }
    }
}
