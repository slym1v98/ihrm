<?php
namespace App\Modules\Training\Application\Commands;
class CancelEnrollmentCommand { public function __construct(public readonly string $id) {} }
