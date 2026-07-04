<?php
namespace App\Modules\Workflow\Domain\Aggregates\WorkflowDelegation;
use Ramsey\Uuid\Uuid;
final class WorkflowDelegationId
{
    public function __construct(private string $value) {}
    public static function new(): self { return new self((string) Uuid::uuid4()); }
    public function value(): string { return $this->value; }
}
