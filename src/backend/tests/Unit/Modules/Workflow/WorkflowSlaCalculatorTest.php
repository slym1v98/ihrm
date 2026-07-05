<?php

namespace Tests\Unit\Modules\Workflow;

use App\Modules\Workflow\Application\Services\WorkflowSlaCalculator;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class WorkflowSlaCalculatorTest extends TestCase
{
    private WorkflowSlaCalculator $calc;

    private array $wh;

    protected function setUp(): void
    {
        $this->calc = new WorkflowSlaCalculator;
        $this->wh = [
            'mon' => ['start' => 8, 'end' => 17],
            'tue' => ['start' => 8, 'end' => 17],
            'wed' => ['start' => 8, 'end' => 17],
            'thu' => ['start' => 8, 'end' => 17],
            'fri' => ['start' => 8, 'end' => 17],
            'sat' => null,
            'sun' => null,
        ];
    }

    public function test_basic_sla_within_same_day(): void
    {
        $start = CarbonImmutable::parse('2026-07-06 09:00:00');
        $deadline = $this->calc->calculateDeadline($start, 3, $this->wh);
        $this->assertEquals('2026-07-06 12:00:00', $deadline->format('Y-m-d H:i:s'));
    }

    public function test_sla_spans_overnight_skips_non_working(): void
    {
        $start = CarbonImmutable::parse('2026-07-06 15:00:00');
        $deadline = $this->calc->calculateDeadline($start, 4, $this->wh);
        $this->assertEquals('2026-07-07 10:00:00', $deadline->format('Y-m-d H:i:s'));
    }

    public function test_sla_spans_weekend(): void
    {
        $start = CarbonImmutable::parse('2026-07-03 15:00:00');
        $deadline = $this->calc->calculateDeadline($start, 4, $this->wh);
        $this->assertEquals('2026-07-06 10:00:00', $deadline->format('Y-m-d H:i:s'));
    }

    public function test_sla_exact_full_day(): void
    {
        $start = CarbonImmutable::parse('2026-07-06 08:00:00');
        $deadline = $this->calc->calculateDeadline($start, 9, $this->wh);
        $this->assertEquals('2026-07-06 17:00:00', $deadline->format('Y-m-d H:i:s'));
    }

    public function test_sla_exact_multi_day(): void
    {
        $start = CarbonImmutable::parse('2026-07-06 08:00:00');
        $deadline = $this->calc->calculateDeadline($start, 18, $this->wh);
        $this->assertEquals('2026-07-07 17:00:00', $deadline->format('Y-m-d H:i:s'));
    }
}
