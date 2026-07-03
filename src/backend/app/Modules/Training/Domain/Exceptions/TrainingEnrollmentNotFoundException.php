<?php
namespace App\Modules\Training\Domain\Exceptions;
class TrainingEnrollmentNotFoundException extends \RuntimeException {
    public function __construct(string $id) { parent::__construct("TrainingEnrollmentNotFoundException: $id"); }
}
