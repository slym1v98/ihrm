<?php

namespace App\Modules\Workflow\Application\Services;

use App\Modules\Workflow\Domain\Exceptions\WorkflowConditionEvaluationException;

class ConditionEvaluator
{
    /**
     * Evaluate a condition tree against the given context.
     *
     * @param array|null $condition Condition tree or null (passthrough).
     * @param array<string, mixed> $context Key-value data for field resolution.
     * @return bool
     * @throws WorkflowConditionEvaluationException
     */
    public function evaluate(?array $condition, array $context): bool
    {
        if ($condition === null) {
            return true;
        }

        // Logical operator — resolve children
        if (isset($condition['op'])) {
            $op = $this->normalizeOp($condition['op']);

            return match ($op) {
                'and' => $this->evaluateAnd($condition['conditions'] ?? [], $context),
                'or'  => $this->evaluateOr($condition['conditions'] ?? [], $context),
                'not' => !$this->evaluate($condition['condition'] ?? null, $context),
                default => $this->evaluateComparison($condition, $context),
            };
        }

        // Treat a bare condition object (field + op + value) as a comparison
        if (isset($condition['field'])) {
            return $this->evaluateComparison($condition, $context);
        }

        throw new WorkflowConditionEvaluationException('Malformed condition: missing "field" or "op"');
    }

    /**
     * Normalise operator aliases.
     */
    private function normalizeOp(string $op): string
    {
        return match ($op) {
            '=', '==', '===', 'eq'  => 'eq',
            '!=', '!==', 'neq'      => 'neq',
            '>' , 'gt'              => 'gt',
            '>=', 'gte'             => 'gte',
            '<' , 'lt'              => 'lt',
            '<=', 'lte'             => 'lte',
            'in'                    => 'in',
            'nin', 'not_in'         => 'nin',
            'exists'                => 'exists',
            'and', 'or', 'not'      => $op,
            default                 => throw new WorkflowConditionEvaluationException("Unknown operator: $op"),
        };
    }

    private function evaluateAnd(array $conditions, array $context): bool
    {
        foreach ($conditions as $cond) {
            if (!$this->evaluate($cond, $context)) {
                return false;
            }
        }
        return true;
    }

    private function evaluateOr(array $conditions, array $context): bool
    {
        foreach ($conditions as $cond) {
            if ($this->evaluate($cond, $context)) {
                return true;
            }
        }
        return false;
    }

    private function evaluateComparison(array $condition, array $context): bool
    {
        $field = $condition['field'] ?? null;
        $op    = $this->normalizeOp($condition['op'] ?? 'eq');
        $value = $condition['value'] ?? null;

        // Special handling so "exists" can work without a value key
        if ($op === 'exists') {
            return array_key_exists($field, $context);
        }

        $actual = $context[$field] ?? null;

        // Missing context field → comparison always false (except exists, handled above)
        if (!array_key_exists($field, $context)) {
            return false;
        }

        return match ($op) {
            'eq'   => $actual === $value,
            'neq'  => $actual !== $value,
            'gt'   => $actual > $value,
            'gte'  => $actual >= $value,
            'lt'   => $actual < $value,
            'lte'  => $actual <= $value,
            'in'   => is_array($value) && in_array($actual, $value, true),
            'nin'  => is_array($value) && !in_array($actual, $value, true),
            default => throw new WorkflowConditionEvaluationException("Unknown comparison operator: $op"),
        };
    }
}
