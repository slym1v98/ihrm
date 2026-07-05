<?php

namespace Tests\Unit\Modules\Recruitment\Domain;

use App\Modules\Recruitment\Domain\Aggregates\Candidate\Candidate;
use App\Modules\Recruitment\Domain\Aggregates\Candidate\CandidateId;
use App\Modules\Recruitment\Domain\Aggregates\Interview\Interview;
use App\Modules\Recruitment\Domain\Aggregates\Interview\InterviewId;
use App\Modules\Recruitment\Domain\Aggregates\Offer\Offer;
use App\Modules\Recruitment\Domain\Aggregates\Offer\OfferId;
use App\Modules\Recruitment\Domain\Aggregates\RecruitmentRequisition\RecruitmentRequisition;
use App\Modules\Recruitment\Domain\Aggregates\RecruitmentRequisition\RecruitmentRequisitionId;
use App\Modules\Recruitment\Domain\ValueObjects\CandidateSource;
use App\Modules\Recruitment\Domain\ValueObjects\CandidateStatus;
use App\Modules\Recruitment\Domain\ValueObjects\OfferStatus;
use App\Modules\Recruitment\Domain\ValueObjects\RequisitionStatus;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class RecruitmentDomainTest extends TestCase
{
    public function test_requisition_transitions(): void
    {
        $req = RecruitmentRequisition::create(RecruitmentRequisitionId::generate(), 'dept-1', 'Developer', 2, 'Growth', 'admin');
        $this->assertSame(RequisitionStatus::Draft, $req->getStatus());
        $req->submit('wfl-1');
        $this->assertSame(RequisitionStatus::PendingApproval, $req->getStatus());
        $req->approve(CarbonImmutable::now());
        $this->assertSame(RequisitionStatus::Open, $req->getStatus());
        $this->assertNotNull($req->getOpenedAt());
    }

    public function test_candidate_transitions(): void
    {
        $c = Candidate::create(CandidateId::generate(), 'req-1', 'Alice', 'alice@e.com', null, CandidateSource::Linkedin);
        $this->assertSame(CandidateStatus::New, $c->getStatus());
        $c->moveTo(CandidateStatus::Screening);
        $this->assertSame(CandidateStatus::Screening, $c->getStatus());
    }

    public function test_scorecard_immutability(): void
    {
        $i = Interview::schedule(InterviewId::generate(), 'c-1', 'r-1', ['int-1'], CarbonImmutable::parse('2026-07-10 10:00'));
        $i->submitScorecard('int-1', 8, 'Good', CarbonImmutable::now());
        $this->expectException(\InvalidArgumentException::class);
        $i->submitScorecard('int-1', 9, 'Again', CarbonImmutable::now());
    }

    public function test_offer_requires_accepted_before_terminal(): void
    {
        $o = Offer::create(OfferId::generate(), 'c-1', 'r-1', ['salary' => 5000], 'admin');
        $this->assertSame(OfferStatus::Draft, $o->getStatus());
        $o->accept(CarbonImmutable::now());
        $this->assertSame(OfferStatus::Accepted, $o->getStatus());
        $this->expectException(\InvalidArgumentException::class);
        $o->accept(CarbonImmutable::now());
    }
}
