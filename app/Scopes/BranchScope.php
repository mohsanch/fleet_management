<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class BranchScope implements Scope
{
    protected static $preventRecursion = false;

    public function apply(Builder $builder, Model $model)
    {
        if (self::$preventRecursion) {
            return;
        }

        self::$preventRecursion = true;

        try {
            // Bypass scoping if we are retrieving the currently authenticated user by ID
            if ($model instanceof \App\Models\User) {
                $userId = auth()->id();
                if ($userId) {
                    foreach ($builder->getQuery()->wheres as $where) {
                        if (isset($where['column']) && 
                            ($where['column'] === 'id' || $where['column'] === $model->getTable() . '.id') && 
                            isset($where['value']) && 
                            $where['value'] == $userId) {
                            return;
                        }
                    }
                }
            }

            if (auth()->check()) {
                $user = auth()->user();

                // If user is restricted to a branch (i.e. has a branch_id assigned), enforce it strictly
                if (!in_array($user->user_type, ['super_admin']) && $user->branch_id !== null) {
                    $builder->where($model->getTable() . '.branch_id', $user->branch_id);
                }
                // For global/super admin, check if they have selected a specific branch filter in the session
                elseif (session()->has('active_branch_id')) {
                    $branchId = session('active_branch_id');
                    if ($branchId !== 'all' && $branchId !== '') {
                        $builder->where($model->getTable() . '.branch_id', $branchId);
                    }
                }
            }
        } finally {
            self::$preventRecursion = false;
        }
    }
}
