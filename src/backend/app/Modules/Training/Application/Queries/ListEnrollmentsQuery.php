<?php

namespace App\Modules\Training\Application\Queries;

class ListEnrollmentsQuery
{
    public function __construct(public readonly string $sessionId) {}
}
