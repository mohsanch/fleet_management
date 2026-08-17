<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DriverSalary extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'driver_id', 'salary_period', 'gross_salary', 'fine', 
        'pasgi_adjustment', 'other_adjustment', 'net_payable', 
        'payment_date', 'payment_status', 'remarks', 'created_by'
    ];

    protected static function boot()
    {
        parent::boot();

        // Automatic Calculation: Net Driver Salary = Gross Salary - Fine - Pasgi Adjustment ± Other Adjustments
        static::saving(function ($model) {
            $model->net_payable = ($model->gross_salary ?? 0) 
                                - ($model->fine ?? 0) 
                                - ($model->pasgi_adjustment ?? 0) 
                                + ($model->other_adjustment ?? 0);
        });

        static::saved(function ($model) {
            if ($model->pasgi_adjustment > 0) {
                \App\Models\PasgiAdjustment::updateOrCreate(
                    ['salary_id' => $model->id],
                    [
                        'driver_id' => $model->driver_id,
                        'amount' => $model->pasgi_adjustment,
                        'date' => $model->payment_date ?? date('Y-m-d'),
                        'remarks' => "Deducted from Salary (Period: {$model->salary_period})",
                        'created_by' => $model->created_by ?? auth()->id()
                    ]
                );
            } else {
                $model->adjustments()->delete();
            }
        });

        static::deleted(function ($model) {
            $model->adjustments()->delete();
        });
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(PasgiAdjustment::class, 'salary_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

