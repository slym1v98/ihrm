<?php
namespace App\Modules\Asset\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AssetAssignmentModel extends Model
{
    use HasUuids;

    protected $table = 'asset_assignments';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'asset_item_id', 'employee_id', 'issued_at',
        'expected_return_at', 'condition_on_issue', 'status',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
            'expected_return_at' => 'datetime',
        ];
    }
}
