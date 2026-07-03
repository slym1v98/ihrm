<?php
namespace App\Modules\Training\Domain\Events;
class AttendanceRecorded {
    public function __construct(public readonly string $entityId) {}
}
