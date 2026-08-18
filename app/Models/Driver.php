<?php

namespace App\Models;

use App\Models\Attachment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Traits\BelongsToBranch;

class Driver extends Model
{
    use BelongsToBranch;

    protected $fillable = ['name', 'contact', 'phone', 'base_salary', 'status', 'license_number', 'branch_id'];

    public function getPhoneAttribute()
    {
        return $this->contact;
    }

    public function setPhoneAttribute($value)
    {
        $this->attributes['contact'] = $value;
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(DriverVehicleAssignment::class);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'assigned_driver_id');
    }

    public function salaries(): HasMany
    {
        return $this->hasMany(DriverSalary::class);
    }

    public function pasgiAdvances(): HasMany
    {
        return $this->hasMany(PasgiAdvance::class);
    }

    public function pasgiAdjustments(): HasMany
    {
        return $this->hasMany(PasgiAdjustment::class);
    }

    public function getRemainingPasgiAttribute(): float
    {
        return $this->pasgiAdvances()->sum('amount') - $this->pasgiAdjustments()->sum('amount');
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
