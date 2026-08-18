<?php

namespace App\Models;

use App\Models\Attachment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Traits\BelongsToBranch;

class Vehicle extends Model
{
    use BelongsToBranch;

    protected $fillable = ['vehicle_number', 'registration_name', 'type', 'assigned_driver_id', 'status', 'branch_id'];

    protected static function booted()
    {
        static::created(function ($vehicle) {
            if ($vehicle->assigned_driver_id) {
                DriverVehicleAssignment::create([
                    'driver_id' => $vehicle->assigned_driver_id,
                    'vehicle_id' => $vehicle->id,
                    'assigned_from' => now()->toDateString(),
                ]);
            }
        });

        static::updating(function ($vehicle) {
            if ($vehicle->isDirty('assigned_driver_id')) {
                $oldDriverId = $vehicle->getOriginal('assigned_driver_id');
                $newDriverId = $vehicle->assigned_driver_id;

                if ($oldDriverId) {
                    DriverVehicleAssignment::where('vehicle_id', $vehicle->id)
                        ->where('driver_id', $oldDriverId)
                        ->whereNull('assigned_to')
                        ->update(['assigned_to' => now()->toDateString()]);
                }

                if ($newDriverId) {
                    DriverVehicleAssignment::where('driver_id', $newDriverId)
                        ->whereNull('assigned_to')
                        ->update(['assigned_to' => now()->toDateString()]);

                    DriverVehicleAssignment::create([
                        'driver_id' => $newDriverId,
                        'vehicle_id' => $vehicle->id,
                        'assigned_from' => now()->toDateString(),
                    ]);
                }
            }
        });
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'assigned_driver_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(DriverVehicleAssignment::class);
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }

    public function dailyData(): HasMany
    {
        return $this->hasMany(FleetDailyData::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function storeItems(): HasMany
    {
        return $this->hasMany(StoreItem::class);
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
