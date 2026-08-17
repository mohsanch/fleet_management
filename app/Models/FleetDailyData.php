<?php

namespace App\Models;

use App\Models\Attachment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FleetDailyData extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'date', 'vehicle_id', 'driver_id', 'pasgi_given', 
        'daily_diesel_amount', 'daily_diesel_liters', 
        'main_km', 'local_km', 'total_km', 'remarks', 'created_by'
    ];

    protected static function boot()
    {
        parent::boot();

        // Automatic Calculation: Total Daily KM = Main KM + Area/Local KM
        static::saving(function ($model) {
            $model->total_km = ($model->main_km ?? 0) + ($model->local_km ?? 0);
        });
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
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

