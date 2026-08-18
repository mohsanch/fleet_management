<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Traits\BelongsToBranch;

class Employee extends Model
{
    use BelongsToBranch;

    protected $fillable = ['name', 'designation', 'contact', 'base_salary', 'status', 'branch_id'];

    public function advances(): HasMany
    {
        return $this->hasMany(EmployeeAdvance::class);
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(EmployeeAdjustment::class);
    }

    public function salaries(): HasMany
    {
        return $this->hasMany(EmployeeSalary::class);
    }

    public function getRemainingAdvanceAttribute(): float
    {
        return $this->advances()->sum('amount') - $this->adjustments()->sum('amount');
    }
}
