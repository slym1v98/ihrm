<?php
namespace App\Modules\Training\Domain\Exceptions;
class TrainingResultNotFoundException extends \RuntimeException {
    public function __construct(string $id) { parent::__construct("TrainingResultNotFoundException: $id"); }
}
