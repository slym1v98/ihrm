<?php

namespace Tests\Unit\Modules\Workflow;

use App\Modules\Workflow\Application\Services\ConditionEvaluator;
use App\Modules\Workflow\Domain\Exceptions\WorkflowConditionEvaluationException;
use PHPUnit\Framework\TestCase;

class ConditionEvaluatorTest extends TestCase
{
    private ConditionEvaluator $evaluator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->evaluator = new ConditionEvaluator();
    }

    // --- Passthrough ---

    public function test_null_condition_returns_true(): void
    {
        self::assertTrue($this->evaluator->evaluate(null, []));
    }

    // --- Logical operators ---

    public function test_and_condition_with_gte_and_in(): void
    {
        $condition = [
            'op' => 'and',
            'conditions' => [
                ['field' => 'duration_days', 'op' => 'gte', 'value' => 3],
                ['field' => 'leave_type_code', 'op' => 'in', 'value' => ['annual', 'sick']],
            ],
        ];
        $context = ['duration_days' => 5, 'leave_type_code' => 'annual'];
        self::assertTrue($this->evaluator->evaluate($condition, $context));
    }

    public function test_and_returns_false_when_one_child_fails(): void
    {
        $condition = [
            'op' => 'and',
            'conditions' => [
                ['field' => 'a', 'op' => 'eq', 'value' => 1],
                ['field' => 'b', 'op' => 'eq', 'value' => 2],
            ],
        ];
        self::assertFalse($this->evaluator->evaluate($condition, ['a' => 1, 'b' => 99]));
    }

    public function test_or_returns_true_when_one_child_passes(): void
    {
        $condition = [
            'op' => 'or',
            'conditions' => [
                ['field' => 'a', 'op' => 'eq', 'value' => 1],
                ['field' => 'b', 'op' => 'eq', 'value' => 2],
            ],
        ];
        self::assertTrue($this->evaluator->evaluate($condition, ['a' => 99, 'b' => 2]));
    }

    public function test_or_returns_false_when_all_children_fail(): void
    {
        $condition = [
            'op' => 'or',
            'conditions' => [
                ['field' => 'a', 'op' => 'eq', 'value' => 1],
                ['field' => 'b', 'op' => 'eq', 'value' => 2],
            ],
        ];
        self::assertFalse($this->evaluator->evaluate($condition, ['a' => 99, 'b' => 99]));
    }

    public function test_not_condition_negates_inner_result(): void
    {
        $condition = [
            'op' => 'not',
            'condition' => ['field' => 'duration_days', 'op' => 'lt', 'value' => 3],
        ];
        self::assertTrue($this->evaluator->evaluate($condition, ['duration_days' => 5]));
    }

    public function test_not_negates_false_to_true(): void
    {
        $condition = [
            'op' => 'not',
            'condition' => ['field' => 'x', 'op' => 'eq', 'value' => 1],
        ];
        self::assertTrue($this->evaluator->evaluate($condition, ['x' => 2]));
    }

    // --- Missing field ---

    public function test_missing_field_returns_false_for_comparison(): void
    {
        $condition = ['field' => 'manager_id', 'op' => 'eq', 'value' => 'u-1'];
        self::assertFalse($this->evaluator->evaluate($condition, []));
    }

    // --- Operator symbols ---

    public function test_symbol_aliases(): void
    {
        $e = $this->evaluator;
        // eq aliases
        self::assertTrue($e->evaluate(['field' => 'a', 'op' => '=', 'value' => 1], ['a' => 1]));
        self::assertTrue($e->evaluate(['field' => 'a', 'op' => '==', 'value' => 1], ['a' => 1]));
        self::assertTrue($e->evaluate(['field' => 'a', 'op' => '===', 'value' => 1], ['a' => 1]));
        // neq aliases
        self::assertFalse($e->evaluate(['field' => 'a', 'op' => '!=', 'value' => 1], ['a' => 1]));
        self::assertFalse($e->evaluate(['field' => 'a', 'op' => '!==', 'value' => 1], ['a' => 1]));
        // gt aliases
        self::assertTrue($e->evaluate(['field' => 'a', 'op' => '>', 'value' => 1], ['a' => 2]));
        // gte aliases
        self::assertTrue($e->evaluate(['field' => 'a', 'op' => '>=', 'value' => 1], ['a' => 1]));
        // lt aliases
        self::assertTrue($e->evaluate(['field' => 'a', 'op' => '<', 'value' => 2], ['a' => 1]));
        // lte aliases
        self::assertTrue($e->evaluate(['field' => 'a', 'op' => '<=', 'value' => 2], ['a' => 2]));
        // nin alias
        self::assertTrue($e->evaluate(['field' => 'a', 'op' => 'not_in', 'value' => [1, 2]], ['a' => 3]));
    }

    // --- Exists ---

    public function test_exists_returns_true_when_field_present(): void
    {
        self::assertTrue($this->evaluator->evaluate(
            ['field' => 'manager_id', 'op' => 'exists'],
            ['manager_id' => 'u-1']
        ));
    }

    public function test_exists_returns_false_when_field_absent(): void
    {
        self::assertFalse($this->evaluator->evaluate(
            ['field' => 'manager_id', 'op' => 'exists'],
            []
        ));
    }

    public function test_exists_returns_true_for_null_value(): void
    {
        self::assertTrue($this->evaluator->evaluate(
            ['field' => 'notes', 'op' => 'exists'],
            ['notes' => null]
        ));
    }

    // --- Nin ---

    public function test_nin_returns_true_when_not_in_list(): void
    {
        self::assertTrue($this->evaluator->evaluate(
            ['field' => 'type', 'op' => 'nin', 'value' => ['annual', 'sick']],
            ['type' => 'personal']
        ));
    }

    public function test_nin_returns_false_when_in_list(): void
    {
        self::assertFalse($this->evaluator->evaluate(
            ['field' => 'type', 'op' => 'nin', 'value' => ['annual', 'sick']],
            ['type' => 'sick']
        ));
    }

    // --- Exceptions ---

    public function test_unknown_op_throws_exception(): void
    {
        $this->expectException(WorkflowConditionEvaluationException::class);
        $this->evaluator->evaluate(['field' => 'a', 'op' => 'bogus', 'value' => 1], ['a' => 1]);
    }

    public function test_malformed_condition_no_field_no_op_throws(): void
    {
        $this->expectException(WorkflowConditionEvaluationException::class);
        $this->evaluator->evaluate(['foo' => 'bar'], []);
    }

    // --- Edge: empty conditions array ---

    public function test_and_with_no_conditions_returns_true(): void
    {
        self::assertTrue($this->evaluator->evaluate(
            ['op' => 'and', 'conditions' => []],
            []
        ));
    }

    public function test_or_with_no_conditions_returns_false(): void
    {
        self::assertFalse($this->evaluator->evaluate(
            ['op' => 'or', 'conditions' => []],
            []
        ));
    }
}
