<?php

namespace App\Http\Controllers;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('roles')
            ->when($request->search, fn($q) => $q->where(fn($sub) => $sub->where('name', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%")))
            ->when($request->role, fn($q) => $q->whereHas('roles', fn($r) => $r->where('name', $request->role)))
            ->when($request->status !== null && $request->status !== '', fn($q) => $q->where('is_active', $request->status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $roles = Role::all();
        return view('users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = Role::all();
        // Derive user_types dynamically from roles (snake_case)
        $userTypes = $roles->map(fn($r) => strtolower(str_replace(' ', '_', $r->name)))->unique()->values();
        return view('users.create', compact('roles', 'userTypes'));
    }

    public function store(Request $request)
    {
        $validUserTypes = Role::all()->map(fn($r) => strtolower(str_replace(' ', '_', $r->name)))->toArray();

        $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'  => ['required', Password::defaults()],
            'user_type' => ['required', 'string', 'in:' . implode(',', $validUserTypes)],
            'is_active' => ['required', 'boolean'],
            'role'      => ['required', 'string', 'exists:roles,name'],
        ]);

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'user_type' => $request->user_type,
            'is_active' => $request->is_active,
        ]);

        $user->assignRole($request->role);

        activity_log('User Created', "New user \"{$user->name}\" ({$user->email}) created.");

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $userTypes = $roles->map(fn($r) => strtolower(str_replace(' ', '_', $r->name)))->unique()->values();
        $userRole = $user->roles->first()?->name;
        return view('users.edit', compact('user', 'roles', 'userTypes', 'userRole'));
    }

    public function update(Request $request, User $user)
    {
        $validUserTypes = Role::all()->map(fn($r) => strtolower(str_replace(' ', '_', $r->name)))->toArray();

        $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password'  => ['nullable', Password::defaults()],
            'user_type' => ['required', 'string', 'in:' . implode(',', $validUserTypes)],
            'is_active' => ['required', 'boolean'],
            'role'      => ['required', 'string', 'exists:roles,name'],
        ]);

        $data = [
            'name'      => $request->name,
            'email'     => $request->email,
            'user_type' => $request->user_type,
            'is_active' => $request->is_active,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);
        $user->syncRoles([$request->role]);

        activity_log('User Updated', "User \"{$user->name}\" profile updated.");

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'You cannot delete your own account.');
        }

        $name = $user->name;
        $user->delete();

        activity_log('User Deleted', "User \"{$name}\" was deleted.");

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
