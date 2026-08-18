<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Traits\BelongsToBranch;

class Income extends Model
{
    use SoftDeletes, BelongsToBranch;
    protected $fillable = ['category_id', 'amount', 'date', 'description', 'reference_source', 'created_by', 'branch_id'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
