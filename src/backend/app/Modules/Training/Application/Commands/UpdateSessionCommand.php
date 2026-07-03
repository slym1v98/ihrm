<?php
namespace App\Modules\Training\Application\Commands;
class UpdateSessionCommand { public function __construct(public readonly string $id, public readonly string $code, public readonly string $name, public readonly string $startDate, public readonly string $endDate, public readonly ?string $location=null, public readonly ?string $instructor=null, public readonly ?int $maxParticipants=null) {} }
