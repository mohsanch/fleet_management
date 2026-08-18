<?php

namespace App\Models;

use App\Models\Attachment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Traits\BelongsToBranch;

class Maintenance extends Model
{
    use SoftDeletes, BelongsToBranch;
    protected $fillable = ['vehicle_id', 'maintenance_date', 'maintenance_type', 'amount', 'vendor', 'invoice_number', 'remarks', 'created_by', 'branch_id'];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}

