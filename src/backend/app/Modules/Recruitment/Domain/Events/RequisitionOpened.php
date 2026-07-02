<?php
namespace App\Modules\Recruitment\Domain\Events;
class RequisitionOpened { public function __construct(public readonly array $payload) {} }
