<?php

namespace App\Modules\Onboarding\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

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
