<?php
namespace App\Modules\Training\Application\Commands;
class UpdateCourseCommand { public function __construct(public readonly string $id, public readonly string $code, public readonly string $name, public readonly ?string $description=null, public readonly ?string $category=null, public readonly ?int $defaultDurationHours=null, public readonly ?int $maxParticipants=null) {} }
