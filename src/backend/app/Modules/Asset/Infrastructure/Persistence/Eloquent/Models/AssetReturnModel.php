<?php
namespace App\Modules\Asset\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AssetReturnModel extends Model
{
    use HasUuids;

    protected $table = 'asset_returns';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'asset_assignment_id', 'returned_at',
        'condition_on_return', 'notes', 'settlement_amount',
    ];

    protected function casts(): array
    {
        return [
            'returned_at' => 'datetime',
            'settlement_amount' => 'decimal:2',
        ];
    }
}
