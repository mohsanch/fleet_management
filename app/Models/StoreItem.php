<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreItem extends Model
{
    use SoftDeletes;
    protected $fillable = ['item_name', 'quantity', 'amount', 'date', 'vehicle_id', 'vendor', 'remarks', 'created_by'];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

