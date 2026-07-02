<?php
namespace App\Modules\Recruitment\Infrastructure\Persistence\Repositories;
use App\Modules\Recruitment\Domain\Aggregates\Offer\Offer;
use App\Modules\Recruitment\Domain\Aggregates\Offer\OfferId;
use App\Modules\Recruitment\Domain\Repositories\OfferRepositoryInterface;
use App\Modules\Recruitment\Domain\ValueObjects\OfferStatus;
use App\Modules\Recruitment\Infrastructure\Persistence\Eloquent\OfferModel;
use Carbon\CarbonImmutable;

class EloquentOfferRepository implements OfferRepositoryInterface {
    public function __construct(private OfferModel $model) {}
    public function findById(OfferId $id): ?Offer { $r=$this->model->find($id->value()); return $r?self::toDomain($r):null; }
    public function findByCandidateId(string $candidateId): ?Offer { $r=$this->model->where('candidate_id',$candidateId)->first(); return $r?self::toDomain($r):null; }
    public function list(): array { return $this->model->orderByDesc('created_at')->get()->map(fn($r)=>self::toDomain($r))->all(); }
    public function save(Offer $o): void {
        $this->model->updateOrCreate(['id'=>(string)$o->getId()],[
            'candidate_id'=>$o->getCandidateId(),
            'requisition_id'=>$o->getRequisitionId(),
            'terms'=>$o->getTerms(),
            'status'=>$o->getStatus()->value,
            'accepted_at'=>$o->getAcceptedAt()?->toDateTimeString(),
            'rejected_at'=>$o->getRejectedAt()?->toDateTimeString(),
            'created_by'=>$o->getCreatedBy(),
        ]);
    }
    public static function toDomain(OfferModel $m): Offer {
        return Offer::reconstitute(new OfferId($m->id),$m->candidate_id,$m->requisition_id,$m->terms??[],OfferStatus::from($m->status),$m->accepted_at?CarbonImmutable::parse($m->accepted_at):null,$m->rejected_at?CarbonImmutable::parse($m->rejected_at):null,$m->created_by);
    }
}
