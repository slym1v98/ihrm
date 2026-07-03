<?php

namespace Tests\Unit\Modules\Onboarding;

use App\Modules\Onboarding\Domain\ValueObjects\TemplateRules;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TemplateRulesTest extends TestCase
{
    #[Test]
    public function matches_with_no_filters(): void
    {
        $rules = new TemplateRules();
        $this->assertTrue($rules->matches('dept-1', 'pos-1', 'loc-1', 'full-time'));
    }

    #[Test]
    public function matches_with_department_filter(): void
    {
        $rules = new TemplateRules(departments: ['dept-1', 'dept-2']);
        $this->assertTrue($rules->matches('dept-1', 'pos-1', 'loc-1', 'full-time'));
        $this->assertFalse($rules->matches('dept-3', 'pos-1', 'loc-1', 'full-time'));
    }

    #[Test]
    public function matches_all_filters(): void
    {
        $rules = new TemplateRules(
            departments: ['dept-1'],
            positions: ['pos-1'],
            locations: ['loc-1'],
            employmentTypes: ['full-time'],
        );
        $this->assertTrue($rules->matches('dept-1', 'pos-1', 'loc-1', 'full-time'));
        $this->assertFalse($rules->matches('dept-1', 'pos-2', 'loc-1', 'full-time'));
    }

    #[Test]
    public function add_and_remove_task(): void
    {
        $rules = new TemplateRules();
        $rules->addTask('Test task', null, 'department', 'it', -3, false, true, 1);
        $this->assertCount(1, $rules->getTasks());

        $rules->removeTask(1);
        $this->assertCount(0, $rules->getTasks());
    }

    #[Test]
    public function from_array_and_to_array_roundtrip(): void
    {
        $data = [
            'departments' => ['dept-1'],
            'positions' => [],
            'locations' => [],
            'employment_types' => ['full-time'],
            'tasks' => [
                ['title' => 'Laptop', 'owner_type' => 'department', 'owner_id' => 'it', 'due_days' => -7, 'requires_approval' => true, 'is_pre_start' => true, 'sort_order' => 1],
            ],
        ];
        $rules = TemplateRules::fromArray($data);
        $this->assertTrue($rules->matches('dept-1', null, null, 'full-time'));
        $this->assertCount(1, $rules->getTasks());

        $roundtripped = $rules->toArray();
        $this->assertSame($data['departments'], $roundtripped['departments']);
        $this->assertCount(1, $roundtripped['tasks']);
    }
}
