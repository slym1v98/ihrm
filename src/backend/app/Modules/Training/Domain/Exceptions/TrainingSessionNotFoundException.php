<?php
namespace App\Modules\Training\Domain\Exceptions;
class TrainingSessionNotFoundException extends \RuntimeException {
    public function __construct(string $id) { parent::__construct("TrainingSessionNotFoundException: $id"); }
}
