<?php

namespace App\Modules\Training\Application\Commands;

class RecordAttendanceCommand
{
    public function __construct(public readonly string $id, public readonly array $attendance) {}
}
