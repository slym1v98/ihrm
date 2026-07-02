<?php
namespace App\Modules\Recruitment\Domain\Events;
class RequisitionApproved { public function __construct(public readonly array $payload) {} }
