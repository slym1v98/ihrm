<?php
namespace App\Modules\Performance\Domain\Exceptions;

class GoalNotFoundException extends \RuntimeException
{
    public function __construct(string $id)
    {
        parent::__construct("Goal not found: {$id}");
    }
}
