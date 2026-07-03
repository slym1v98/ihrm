<?php
namespace App\Modules\Training\Domain\Events;
class EnrollmentCancelled {
    public function __construct(public readonly string $entityId) {}
}
