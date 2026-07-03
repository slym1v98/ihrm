<?php
namespace Tests\Unit\Modules\Asset;

use PHPUnit\Framework\TestCase;
use App\Modules\Asset\Domain\Aggregates\AssetAssignment\AssetAssignment;
use App\Modules\Asset\Domain\Aggregates\AssetReturn\AssetReturn;
use App\Modules\Asset\Domain\ValueObjects\AssetAssignmentId;
use App\Modules\Asset\Domain\ValueObjects\AssetAssignmentStatus;
use App\Modules\Asset\Domain\ValueObjects\AssetCondition;
use App\Modules\Asset\Domain\ValueObjects\AssetItemId;
use App\Modules\Asset\Domain\ValueObjects\AssetReturnId;
use App\Modules\Asset\Domain\Exceptions\AssetAssignmentAlreadyReturnedException;

class AssetAssignmentTest extends TestCase
{
    public function test_create_assignment(): void
    {
        $a = AssetAssignment::create(
            AssetAssignmentId::generate(),
            AssetItemId::generate(),
            'emp-1',
            new \DateTimeImmutable(),
            null,
            AssetCondition::Good,
        );
        $this->assertSame(AssetAssignmentStatus::Active, $a->getStatus());
    }

    public function test_complete_return(): void
    {
        $a = AssetAssignment::create(
            AssetAssignmentId::generate(),
            AssetItemId::generate(),
            'emp-1',
            new \DateTimeImmutable(),
            null,
            AssetCondition::Good,
        );
        $a->completeReturn();
        $this->assertSame(AssetAssignmentStatus::Returned, $a->getStatus());
    }

    public function test_cannot_return_again(): void
    {
        $a = AssetAssignment::create(
            AssetAssignmentId::generate(),
            AssetItemId::generate(),
            'emp-1',
            new \DateTimeImmutable(),
            null,
            AssetCondition::Good,
        );
        $a->completeReturn();
        $this->expectException(AssetAssignmentAlreadyReturnedException::class);
        $a->completeReturn();
    }
}
