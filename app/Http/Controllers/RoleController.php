<?php

namespace App\Http\Controllers;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use App\Models\ActivityLog;

class RoleController extends Controller
{
    // ─── Index: clean roles list table ───────────────────────────────────────
    public function index()
    {
        $roles = Role::with('permissions')->withCount('users')->orderBy('name')->get();
        return view('admin.roles.index', compact('roles'));
    }

    // ─── Show create role form ────────────────────────────────────────────────
    public function create()
    {
        $permissions = Permission::orderBy('name')->get();
        $groupedPermissions = $permissions->groupBy(function ($perm) {
            return explode('.', $perm->name)[0] ?? 'general';
        });
        return view('admin.roles.create', compact('permissions', 'groupedPermissions'));
    }

    // ─── Show edit role form ──────────────────────────────────────────────────
    public function edit(Role $role)
    {
        $permissions = Permission::orderBy('name')->get();
        $groupedPermissions = $permissions->groupBy(function ($perm) {
            return explode('.', $perm->name)[0] ?? 'general';
        });
        $role->load('permissions');
        return view('admin.roles.edit', compact('role', 'permissions', 'groupedPermissions'));
    }

    // ─── Store new role ───────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'name'          => ['required', 'string', 'max:100', 'unique:roles,name'],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);

        if ($request->filled('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        ActivityLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'CREATE',
            'model_type'  => 'Role',
            'model_id'    => $role->id,
            'description' => "New role \"{$role->name}\" created with " . count($request->permissions ?? []) . " permission(s).",
        ]);

        return redirect()->route('admin.roles.index')->with('success', "Role \"{$role->name}\" created successfully.");
    }

    // ─── Rename a role ────────────────────────────────────────────────────────
    public function rename(Request $request, Role $role)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:roles,name,' . $role->id],
        ]);

        $oldName = $role->name;
        $role->update(['name' => $request->name]);

        ActivityLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'UPDATE',
            'model_type'  => 'Role',
            'model_id'    => $role->id,
            'description' => "Role renamed from \"{$oldName}\" to \"{$role->name}\".",
        ]);

        return redirect()->route('admin.roles.index')->with('success', "Role renamed to \"{$role->name}\".");
    }

    // ─── Update role name + permissions ──────────────────────────────────────
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name'          => ['required', 'string', 'max:100', 'unique:roles,name,' . $role->id],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        $oldName = $role->name;
        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions ?? []);

        ActivityLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'UPDATE',
            'model_type'  => 'Role',
            'model_id'    => $role->id,
            'description' => "Role \"{$oldName}\" updated (renamed to \"{$role->name}\", permissions synced).",
        ]);

        return redirect()->route('admin.roles.index')->with('success', "Role \"{$role->name}\" updated successfully.");
    }

    // ─── Delete a role ────────────────────────────────────────────────────────
    public function destroy(Role $role)
    {
        if ($role->users()->count() > 0) {
            return redirect()->route('admin.roles.index')
                ->with('error', "Cannot delete \"{$role->name}\" — assigned to {$role->users()->count()} user(s).");
        }

        $name = $role->name;
        $id   = $role->id;
        $role->delete();

        ActivityLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'DELETE',
            'model_type'  => 'Role',
            'model_id'    => $id,
            'description' => "Role \"{$name}\" deleted.",
        ]);

        return redirect()->route('admin.roles.index')->with('success', "Role \"{$name}\" deleted.");
    }
}
