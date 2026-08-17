<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ActivityLog;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;

class SuperAdminController extends Controller
{
    // ─── Dashboard ────────────────────────────────────────────────────────────
    public function dashboard()
    {
        $totalUsers       = User::count();
        $activeUsers      = User::where('is_active', true)->count();
        $inactiveUsers    = User::where('is_active', false)->count();
        $totalRoles       = Role::count();
        $totalPermissions = Permission::count();

        // Users grouped by role for chart
        $usersByRole = Role::withCount('users')->get()->map(fn($r) => [
            'name'  => $r->name,
            'count' => $r->users_count,
        ]);

        // Recent activity (last 25)
        $recentLogs = ActivityLog::with('user')
            ->orderByDesc('created_at')
            ->limit(25)
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers', 'activeUsers', 'inactiveUsers',
            'totalRoles', 'totalPermissions',
            'usersByRole', 'recentLogs'
        ));
    }

    // ─── Users List ──────────────────────────────────────────────────────────
    public function users(Request $request)
    {
        $query = User::with(['roles', 'permissions'])
            ->when($request->search, fn($q) => $q->where(fn($s) =>
                $s->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
            ))
            ->when($request->role, fn($q) => $q->whereHas('roles', fn($r) => $r->where('name', $request->role)))
            ->when($request->status !== null && $request->status !== '', fn($q) => $q->where('is_active', $request->status));

        $users = $query->latest()->paginate(15)->withQueryString();
        $roles = Role::all();

        // Get all permissions grouped by module for direct assignment modal
        $allPermissions = Permission::orderBy('name')->get();
        $groupedPermissions = $allPermissions->groupBy(function ($perm) {
            return explode('.', $perm->name)[0] ?? 'general';
        });

        return view('admin.users.index', compact('users', 'roles', 'groupedPermissions'));
    }

    // ─── Toggle User Active/Inactive ─────────────────────────────────────────
    public function toggleUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';

        ActivityLog::create([
            'user_id'    => auth()->id(),
            'action'     => 'UPDATE',
            'model_type' => 'User',
            'model_id'   => $user->id,
            'description' => "User \"{$user->name}\" {$status} by Super Admin.",
        ]);

        return back()->with('success', "User \"{$user->name}\" has been {$status}.");
    }

    // ─── Reset User Password ─────────────────────────────────────────────────
    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'new_password' => ['required', 'string', 'min:8'],
        ]);

        $user->update(['password' => bcrypt($request->new_password)]);

        ActivityLog::create([
            'user_id'    => auth()->id(),
            'action'     => 'UPDATE',
            'model_type' => 'User',
            'model_id'   => $user->id,
            'description' => "Password reset for user \"{$user->name}\" by Super Admin.",
        ]);

        return back()->with('success', "Password for \"{$user->name}\" has been reset.");
    }

    // ─── Activity Logs ───────────────────────────────────────────────────────
    public function logs(Request $request)
    {
        $query = ActivityLog::with('user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn($q) =>
                $q->where('reason', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%")
                  ->orWhere('model_type', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"))
            );
        }

        if ($request->filled('action')) {
            $query->where('action', strtoupper($request->action));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs  = $query->orderByDesc('created_at')->paginate(25)->withQueryString();
        $users = User::orderBy('name')->get();

        return view('admin.logs.index', compact('logs', 'users'));
    }

    // ─── Update User Direct Permissions ───────────────────────────────────────
    public function updateDirectPermissions(Request $request, User $user)
    {
        $request->validate([
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        // Sync direct permissions to this user (bypassing their role)
        $user->syncPermissions($request->permissions ?? []);

        ActivityLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'UPDATE',
            'model_type'  => 'User',
            'model_id'    => $user->id,
            'description' => "Direct permissions updated for user \"{$user->name}\".",
        ]);

        return back()->with('success', "Direct permissions for \"{$user->name}\" updated successfully.");
    }
}
