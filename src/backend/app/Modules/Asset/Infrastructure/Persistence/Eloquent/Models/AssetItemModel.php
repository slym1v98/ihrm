<?php
namespace App\Modules\Asset\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AssetItemModel extends Model
{
    use HasUuids;

    protected $table = 'asset_items';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'asset_code', 'asset_type', 'name', 'serial_number',
        'condition', 'status', 'notes',
    ];
}
