<?php

namespace Tests\Unit\Modules\Performance;

use PHPUnit\Framework\TestCase;
use App\Modules\Performance\Domain\Aggregates\PerformanceReview\PerformanceReview;
use App\Modules\Performance\Domain\Aggregates\PerformanceReview\PerformanceReviewId;
use App\Modules\Performance\Domain\Exceptions\InvalidStatusTransitionException;
use App\Modules\Performance\Domain\ValueObjects\ReviewStatus;

class PerformanceReviewTest extends TestCase
{
    public function test_review_progresses_through_stages(): void
    {
        $r = PerformanceReview::create(PerformanceReviewId::generate(), 'c1', 'e1');
        $this->assertSame(ReviewStatus::PendingSelf, $r->getStatus());

        $r->submitSelf(['rating' => 4]);
        $this->assertSame(ReviewStatus::SelfCompleted, $r->getStatus());

        $r->submitManager(['rating' => 4]);
        $r->submitHr(['rating' => 4]);
        $r->finalize(4.0);

        $this->assertSame(ReviewStatus::Finalized, $r->getStatus());
        $this->assertSame(4.0, $r->getFinalScore());
        $this->assertNotNull($r->getFinalizedAt());
    }

    public function test_review_rejects_skipping_stage(): void
    {
        $r = PerformanceReview::create(PerformanceReviewId::generate(), 'c1', 'e1');
        $this->expectException(InvalidStatusTransitionException::class);
        $r->submitManager(['rating' => 3]);
    }

    public function test_finalized_review_is_immutable(): void
    {
        $r = PerformanceReview::create(PerformanceReviewId::generate(), 'c1', 'e1');
        $r->submitSelf(['a' => 1]);
        $r->submitManager(['a' => 1]);
        $r->submitHr(['a' => 1]);
        $r->finalize(3.5);

        $this->expectException(InvalidStatusTransitionException::class);
        $r->submitSelf(['a' => 2]);
    }
}
