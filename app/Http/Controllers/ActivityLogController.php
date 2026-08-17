<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = ActivityLog::with('user');

        // Super Admin and Admin see ALL logs; every other role sees only their own
        $canSeeAll = in_array($user->user_type, ['super_admin', 'admin'])
                     || $user->hasRole('Admin');

        if (!$canSeeAll) {
            $query->where('user_id', $user->id);
        }

        // Optional search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('action',     'like', "%{$search}%")
                  ->orWhere('reason',   'like', "%{$search}%")
                  ->orWhere('model_type','like', "%{$search}%");
                // Admin and Super Admin can also search by user name
                if ($canSeeAll) {
                    $q->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"));
                }
            });
        }

        if ($request->filled('action')) {
            $query->where('action', strtoupper($request->action));
        }

        $isAdmin = $canSeeAll;
        $logs      = $query->orderByDesc('created_at')->paginate(25)->withQueryString();

        return view('activity_logs.index', compact('logs', 'isAdmin'));
    }
}
