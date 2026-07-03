<?php

namespace App\Modules\Performance\Application\Commands;

class UpdateTemplateCommand
{
    public function __construct(
        public readonly string $id,
        public readonly string $code,
        public readonly string $name,
        public readonly array $rules,
    ) {}
}
