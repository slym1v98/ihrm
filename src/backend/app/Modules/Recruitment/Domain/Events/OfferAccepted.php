<?php
namespace App\Modules\Recruitment\Domain\Events;
class OfferAccepted { public function __construct(public readonly array $payload) {} }
