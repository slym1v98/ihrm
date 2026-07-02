<?php
namespace App\Modules\Recruitment\Application\CommandHandlers;
use App\Modules\Recruitment\Application\Commands\ConvertCandidateToEmployeeCommand;
use App\Modules\Recruitment\Domain\Aggregates\Candidate\CandidateId;
use App\Modules\Recruitment\Domain\Aggregates\Offer\OfferId;
use App\Modules\Recruitment\Domain\Exceptions\{OfferNotFoundException,CandidateNotFoundException,CandidateConversionException};
use App\Modules\Recruitment\Domain\Repositories\{OfferRepositoryInterface,CandidateRepositoryInterface,RecruitmentRequisitionRepositoryInterface};
use App\Modules\Recruitment\Domain\Aggregates\RecruitmentRequisition\RecruitmentRequisitionId;
use App\Modules\Recruitment\Domain\ValueObjects\OfferStatus;
use App\Modules\Recruitment\Infrastructure\Services\EmployeeConversionService;
class ConvertCandidateToEmployeeHandler {
    public function __construct(private OfferRepositoryInterface $offers, private CandidateRepositoryInterface $candidates, private RecruitmentRequisitionRepositoryInterface $reqs, private EmployeeConversionService $conversion) {}
    public function handle(ConvertCandidateToEmployeeCommand $cmd): string {
        $o=$this->offers->findById(new OfferId($cmd->offerId));
        if(!$o) throw new OfferNotFoundException($cmd->offerId);
        if($o->getStatus()!==OfferStatus::Accepted) throw new CandidateConversionException('Offer must be accepted');
        $c=$this->candidates->findById(new CandidateId($o->getCandidateId()));
        if(!$c) throw new CandidateNotFoundException($o->getCandidateId());
        if($c->getEmployeeId()!==null) throw new CandidateConversionException('Already converted');
        $req=$this->reqs->findById(new RecruitmentRequisitionId($o->getRequisitionId()));
        if(!$req) throw new CandidateConversionException('Requisition not found');
        $empId=$this->conversion->convert($c,$req);
        $c->linkEmployee($empId); $this->candidates->save($c);
        return $empId;
    }
}
