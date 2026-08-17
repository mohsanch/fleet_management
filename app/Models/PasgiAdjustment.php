<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PasgiAdjustment extends Model
{
    protected $fillable = ['driver_id', 'amount', 'date', 'remarks', 'salary_id', 'created_by'];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function salary(): BelongsTo
    {
        return $this->belongsTo(DriverSalary::class, 'salary_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
