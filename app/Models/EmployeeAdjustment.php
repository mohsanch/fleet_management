<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeAdjustment extends Model
{
    protected $fillable = ['employee_id', 'amount', 'date', 'remarks', 'salary_id', 'created_by'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function salary(): BelongsTo
    {
        return $this->belongsTo(EmployeeSalary::class, 'salary_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
