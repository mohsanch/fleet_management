<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    // ─── Index: Module-based permissions page ─────────────────────────────────
    public function index()
    {
        $allPermissions = Permission::orderBy('name')->get();

        // Group permissions by module prefix (e.g. "users.view" → "users")
        $grouped = $allPermissions->groupBy(function ($perm) {
            $parts = explode('.', $perm->name);
            return $parts[0] ?? 'general';
        });

        $roles = Role::with('permissions')->get();

        return view('admin.permissions.index', compact('grouped', 'allPermissions', 'roles'));
    }

    // ─── Store: Single permission ──────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:permissions,name'],
        ]);

        $perm = Permission::create(['name' => $request->name, 'guard_name' => 'web']);

        ActivityLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'CREATE',
            'model_type'  => 'Permission',
            'model_id'    => $perm->id,
            'description' => "Permission \"{$request->name}\" created.",
        ]);

        $redirect = $request->redirect_to === 'permissions'
            ? route('admin.permissions.index')
            : route('admin.roles.index');

        return redirect($redirect)->with('success', "Permission \"{$request->name}\" created successfully.");
    }

    // ─── Bulk: Generate module permissions ────────────────────────────────────
    public function bulk(Request $request)
    {
        $request->validate([
            'module'   => ['required', 'string', 'max:60'],
            'actions'  => ['required', 'array', 'min:1'],
            'actions.*'=> ['string', 'in:view,create,edit,delete,approve,export'],
        ]);

        $module  = strtolower(trim($request->module));
        $created = [];
        $skipped = [];

        foreach ($request->actions as $action) {
            $name = "{$module}.{$action}";
            if (Permission::where('name', $name)->exists()) {
                $skipped[] = $name;
                continue;
            }
            Permission::create(['name' => $name, 'guard_name' => 'web']);
            $created[] = $name;
        }

        if (!empty($created)) {
            ActivityLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'CREATE',
                'model_type'  => 'Permission',
                'description' => 'Bulk created permissions: ' . implode(', ', $created),
            ]);
        }

        $msg = '';
        if (!empty($created)) $msg .= count($created) . ' permission(s) created. ';
        if (!empty($skipped)) $msg .= count($skipped) . ' already existed (skipped).';

        return redirect()->route('admin.permissions.index')->with('success', trim($msg));
    }

    // ─── Destroy: Remove permission ───────────────────────────────────────────
    public function destroy(Permission $permission)
    {
        $name = $permission->name;

        // Remove from all roles first
        foreach (Role::all() as $role) {
            $role->revokePermissionTo($permission);
        }

        $permission->delete();

        ActivityLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'DELETE',
            'model_type'  => 'Permission',
            'description' => "Permission \"{$name}\" deleted.",
        ]);

        $redirect = request('redirect_to') === 'permissions'
            ? route('admin.permissions.index')
            : route('admin.roles.index');

        return redirect($redirect)->with('success', "Permission \"{$name}\" deleted.");
    }
}
