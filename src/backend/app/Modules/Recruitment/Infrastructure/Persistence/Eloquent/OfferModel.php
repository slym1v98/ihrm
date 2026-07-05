<?php

namespace App\Modules\Recruitment\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;

class OfferModel extends Model
{
    protected $table = 'recruitment_offers';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['id', 'candidate_id', 'requisition_id', 'terms', 'status', 'accepted_at', 'rejected_at', 'created_by'];

    protected $casts = ['terms' => 'array', 'accepted_at' => 'datetime', 'rejected_at' => 'datetime'];
}
