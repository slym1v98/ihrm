<?php
namespace App\Modules\Performance\Domain\Exceptions;

class PerformanceReviewNotFoundException extends \RuntimeException
{
    public function __construct(string $id)
    {
        parent::__construct("PerformanceReview not found: {$id}");
    }
}
