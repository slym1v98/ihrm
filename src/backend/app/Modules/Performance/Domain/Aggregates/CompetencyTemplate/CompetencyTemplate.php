<?php

namespace App\Modules\Performance\Domain\Aggregates\CompetencyTemplate;

use App\Modules\Performance\Domain\Events\CompetencyTemplateCreated;

class CompetencyTemplate
{
    private array $recordedEvents = [];

    private function __construct(
        private readonly CompetencyTemplateId $id,
        private string $code,
        private string $name,
        private array $rules,
        private bool $active,
    ) {}

    public static function create(CompetencyTemplateId $id, string $code, string $name, array $rules): self
    {
        $t = new self($id, $code, $name, $rules, true);
        $t->recordedEvents[] = new CompetencyTemplateCreated($id->value);
        return $t;
    }

    public static function reconstitute(CompetencyTemplateId $id, string $code, string $name, array $rules, bool $active): self
    {
        return new self($id, $code, $name, $rules, $active);
    }

    public function update(string $code, string $name, array $rules): void
    {
        $this->code = $code;
        $this->name = $name;
        $this->rules = $rules;
    }

    public function disable(): void { $this->active = false; }

    public function popRecordedEvents(): array { $e=$this->recordedEvents; $this->recordedEvents=[]; return $e; }
    public function getId(): CompetencyTemplateId { return $this->id; }
    public function getCode(): string { return $this->code; }
    public function getName(): string { return $this->name; }
    public function getRules(): array { return $this->rules; }
    public function isActive(): bool { return $this->active; }
}
