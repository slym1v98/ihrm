<?php

namespace Tests\Unit\Modules\Employee\Domain;

use App\Modules\Employee\Domain\Aggregates\Contract\DateRange;
use App\Modules\Employee\Domain\Aggregates\Employee\EmployeeCode;
use App\Modules\Employee\Domain\Aggregates\Employee\EmployeeId;
use App\Modules\Employee\Domain\Aggregates\Employee\PersonalName;
use App\Modules\Employee\Domain\Aggregates\EmployeeDocument\DocumentDescriptor;
use DateTimeImmutable;
use InvalidArgumentException;
use Tests\TestCase;

class ValueObjectTest extends TestCase
{
    public function test_employee_id_generate_from_string_and_equals(): void
    {
        $id = EmployeeId::generate();
        $same = EmployeeId::fromString((string) $id);

        $this->assertTrue($id->equals($same));
    }

    public function test_employee_code_rejects_empty(): void
    {
        $this->expectException(InvalidArgumentException::class);

        EmployeeCode::fromString('');
    }

    public function test_personal_name_requires_first_and_last(): void
    {
        $this->expectException(InvalidArgumentException::class);

        PersonalName::of('Ada', '');
    }

    public function test_date_range_overlap_handles_closed_and_open_ranges(): void
    {
        $jan = new DateRange(new DateTimeImmutable('2026-01-01'), new DateTimeImmutable('2026-01-31'));
        $feb = new DateRange(new DateTimeImmutable('2026-02-01'), new DateTimeImmutable('2026-02-28'));
        $open = new DateRange(new DateTimeImmutable('2026-01-15'));

        $this->assertFalse($jan->overlaps($feb));
        $this->assertTrue($jan->overlaps($open));
        $this->assertTrue($open->overlaps($feb));
    }

    public function test_document_descriptor_rejects_empty_path(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DocumentDescriptor('', 'a.pdf', 'application/pdf', 1);
    }
}
