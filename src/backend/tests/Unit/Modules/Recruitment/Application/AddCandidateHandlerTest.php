<?php

namespace Tests\Unit\Modules\Recruitment\Application;

use App\Modules\Recruitment\Application\CommandHandlers\AddCandidateHandler;
use App\Modules\Recruitment\Application\Commands\AddCandidateCommand;
use App\Modules\Recruitment\Domain\Aggregates\Candidate\Candidate;
use App\Modules\Recruitment\Domain\Exceptions\DuplicateCandidateException;
use App\Modules\Recruitment\Domain\Repositories\CandidateRepositoryInterface;
use PHPUnit\Framework\TestCase;

class AddCandidateHandlerTest extends TestCase
{
    public function test_creates_candidate(): void
    {
        $repo = $this->createMock(CandidateRepositoryInterface::class);
        $repo->method('findByEmail')->willReturn(null);
        $repo->method('findByPhone')->willReturn(null);
        $repo->expects($this->once())->method('save');

        $handler = new AddCandidateHandler($repo);
        $candidate = $handler->handle(new AddCandidateCommand(null, 'Alice', 'alice@example.com', null, 'manual'));

        $this->assertSame('Alice', $candidate->getFullName());
    }

    public function test_duplicate_email_rejected(): void
    {
        $repo = $this->createMock(CandidateRepositoryInterface::class);
        $repo->method('findByEmail')->willReturn($this->createStub(Candidate::class));
        $handler = new AddCandidateHandler($repo);

        $this->expectException(DuplicateCandidateException::class);
        $handler->handle(new AddCandidateCommand(null, 'Alice', 'alice@example.com', null, 'manual'));
    }
}
