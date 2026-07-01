<?php

namespace Tests\Unit\Modules\Employee\Domain;

use App\Modules\Employee\Domain\Aggregates\Contract\Contract;
use App\Modules\Employee\Domain\Aggregates\Contract\ContractId;
use App\Modules\Employee\Domain\Aggregates\Contract\ContractTerm;
use App\Modules\Employee\Domain\Aggregates\Contract\DateRange;
use App\Modules\Employee\Domain\Aggregates\Employee\EmployeeId;
use App\Modules\Employee\Domain\Exceptions\ContractRenewalException;
use DateTimeImmutable;
use Tests\TestCase;

class ContractTest extends TestCase
{
    public function test_definite_contract_requires_end_date(): void
    {
        $this->expectException(ContractRenewalException::class);

        Contract::create(
            ContractId::generate(),
            EmployeeId::generate(),
            'C001',
            new ContractTerm('definite', new DateRange(new DateTimeImmutable('2026-01-01'))),
        );
    }

    public function test_contract_activate_and_renew(): void
    {
        $contract = Contract::create(
            ContractId::generate(),
            EmployeeId::generate(),
            'C001',
            new ContractTerm('indefinite', new DateRange(new DateTimeImmutable('2026-01-01'))),
        );
        $contract->releaseEvents();
        $contract->activate();
        $renewed = $contract->renew(
            ContractId::generate(),
            'C002',
            new ContractTerm('indefinite', new DateRange(new DateTimeImmutable('2026-02-01'))),
        );

        $this->assertCount(1, $contract->releaseEvents());
        $this->assertCount(1, $renewed->releaseEvents());
    }
}
