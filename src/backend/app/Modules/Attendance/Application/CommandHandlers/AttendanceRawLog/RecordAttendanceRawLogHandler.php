<?php

namespace App\Modules\Attendance\Application\CommandHandlers\AttendanceRawLog;

use App\Modules\Attendance\Application\Commands\AttendanceRawLog\RecordAttendanceRawLogCommand;
use App\Modules\Attendance\Domain\Aggregates\AttendanceRawLog\AttendanceRawLog;
use App\Modules\Attendance\Domain\Repositories\AttendanceRawLogRepositoryInterface;
use App\Modules\Attendance\Domain\ValueObjects\GeoPoint;
use Carbon\CarbonImmutable;

class RecordAttendanceRawLogHandler
{
    public function __construct(
        private AttendanceRawLogRepositoryInterface $repository,
    ) {}

    public function handle(RecordAttendanceRawLogCommand $command): AttendanceRawLog
    {
        $rawLog = AttendanceRawLog::record(
            employeeId: $command->employeeId,
            source: $command->source,
            eventType: $command->eventType,
            eventTime: CarbonImmutable::parse($command->eventTime),
            geoPoint: $command->geoPoint ? GeoPoint::fromArray($command->geoPoint['lat'], $command->geoPoint['lng']) : null,
            payload: $command->payload,
        );

        $this->repository->saveAndDispatch($rawLog);

        return $rawLog;
    }
}
