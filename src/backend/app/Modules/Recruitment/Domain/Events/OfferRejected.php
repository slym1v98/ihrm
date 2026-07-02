<?php
namespace App\Modules\Recruitment\Domain\Events;
class OfferRejected { public function __construct(public readonly array $payload) {} }
