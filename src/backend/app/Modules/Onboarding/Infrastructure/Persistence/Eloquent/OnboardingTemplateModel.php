<?php

namespace App\Modules\Onboarding\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class OnboardingTemplateModel extends Model
{
    use HasUuids;

    protected $table = 'onboarding_templates';

    protected $fillable = [
        'id', 'code', 'name', 'rules', 'active',
    ];

    protected function casts(): array
    {
        return [
            'rules' => 'array',
            'active' => 'boolean',
        ];
    }
}
