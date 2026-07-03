<?php
namespace App\Modules\Training\Domain\Events;
class ResultRecorded {
    public function __construct(public readonly string $entityId) {}
}
