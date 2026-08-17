<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeSalary extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'employee_id', 'salary_period', 'gross_salary', 'fine', 
        'advance_adjustment', 'other_adjustment', 'net_payable', 
        'payment_date', 'payment_status', 'remarks', 'created_by'
    ];

    protected static function boot()
    {
        parent::boot();

        // Automatic Calculation: Net Employee Salary = Gross Salary - Fine - Advance Adjustment Â± Other Adjustments
        static::saving(function ($model) {
            $model->net_payable = ($model->gross_salary ?? 0) 
                                - ($model->fine ?? 0) 
                                - ($model->advance_adjustment ?? 0) 
                                + ($model->other_adjustment ?? 0);
        });
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(EmployeeAdjustment::class, 'salary_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

