<?php

namespace App\Traits;

use App\Models\Branch;
use App\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToBranch
{
    public static function bootBelongsToBranch()
    {
        static::addGlobalScope(new BranchScope());

        static::creating(function ($model) {
            if (empty($model->branch_id) && auth()->check()) {
                $user = auth()->user();
                
                // If user is restricted to a branch, auto-assign their branch
                if ($user->branch_id !== null) {
                    $model->branch_id = $user->branch_id;
                }
                // Otherwise, assign the currently switched active session branch for global admins
                elseif (session()->has('active_branch_id')) {
                    $branchId = session('active_branch_id');
                    if ($branchId !== 'all' && $branchId !== '') {
                        $model->branch_id = $branchId;
                    }
                }
            }
        });
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
