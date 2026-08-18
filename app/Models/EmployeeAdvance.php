<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToBranch;

class EmployeeAdvance extends Model
{
    use SoftDeletes, BelongsToBranch;
    protected $fillable = ['employee_id', 'amount', 'date', 'remarks', 'created_by', 'branch_id'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

