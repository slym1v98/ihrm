<?php
namespace Tests\Unit\Modules\Asset;

use PHPUnit\Framework\TestCase;
use App\Modules\Asset\Domain\Aggregates\AssetItem\AssetItem;
use App\Modules\Asset\Domain\ValueObjects\AssetCondition;
use App\Modules\Asset\Domain\ValueObjects\AssetItemId;
use App\Modules\Asset\Domain\ValueObjects\AssetItemStatus;
use App\Modules\Asset\Domain\Exceptions\AssetStatusTransitionException;

class AssetItemTest extends TestCase
{
    public function test_create_item(): void
    {
        $item = AssetItem::create(
            AssetItemId::generate(),
            'AST-001',
            'laptop',
            'MacBook Pro',
            'SN001',
            AssetCondition::New,
            null,
        );
        $this->assertSame('AST-001', $item->getAssetCode());
        $this->assertSame(AssetItemStatus::Available, $item->getStatus());
    }

    public function test_mark_maintenance_from_available(): void
    {
        $item = AssetItem::create(AssetItemId::generate(), 'AST-002', 'laptop', 'Dell', null, AssetCondition::Good, null);
        $item->markStatus(AssetItemStatus::Maintenance);
        $this->assertSame(AssetItemStatus::Maintenance, $item->getStatus());
    }

    public function test_cannot_mark_maintenance_from_assigned(): void
    {
        $item = AssetItem::create(AssetItemId::generate(), 'AST-003', 'laptop', 'ThinkPad', null, AssetCondition::Good, null);
        $item->assign();
        $this->expectException(AssetStatusTransitionException::class);
        $item->markStatus(AssetItemStatus::Maintenance);
    }

    public function test_finish_return_maps_conditions(): void
    {
        $item = AssetItem::create(AssetItemId::generate(), 'AST-005', 'laptop', 'MBP', null, AssetCondition::Good, null);
        $this->assertSame(AssetItemStatus::Available, $item->finishReturn(AssetItemStatus::Available->value));
        $this->assertSame(AssetItemStatus::Maintenance, $item->finishReturn(AssetCondition::Poor->value));
        $this->assertSame(AssetItemStatus::Lost, $item->finishReturn(AssetItemStatus::Lost->value));
        $this->assertSame(AssetItemStatus::Damaged, $item->finishReturn(AssetItemStatus::Damaged->value));
    }
}
