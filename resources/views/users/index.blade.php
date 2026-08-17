@extends('layouts.app')

@section('title', 'User Management')
@section('breadcrumbs', 'Users')
@section('page_title', 'User Management')

@section('content')

<div class="card table-card">
    <div class="chart-header" style="display: flex; flex-direction: column; align-items: flex-start; gap: 16px; width: 100%; border-bottom: none; padding-bottom: 0;">
        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; flex-wrap: wrap; gap: 15px;">
            <div class="chart-title-block">
                <span class="chart-title">System Users</span>
                <span class="chart-subtitle">Manage user profiles, active status, and assign roles</span>
            </div>
            <a href="{{ route('users.create') }}" class="btn-signin" style="padding: 10px 18px; width: auto; display: inline-flex; align-items: center; gap: 8px; font-size: 12px; margin: 0; white-space: nowrap;">
                <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                <span>Add New User</span>
            </a>
        </div>
        <div style="width: 100%; display: flex; justify-content: flex-start; align-items: center; background: rgba(0,0,0,0.02); padding: 12px 16px; border-radius: 8px; border: 1px solid var(--border-color); box-shadow: inset 0 1px 2px rgba(0,0,0,0.02);">
            <form action="{{ route('users.index') }}" method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap; margin: 0; width: 100%;">
                <input type="text" name="search" class="form-input" placeholder="Search name or email..." value="{{ request('search') }}" style="height: 38px; padding: 5px 15px; font-size: 13px; max-width: 220px; margin: 0;">
                
                <select name="role" class="form-input" style="height: 38px; padding: 5px 15px; font-size: 13px; width: auto; margin: 0;">
                    <option value="">All Roles</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->name }}" {{ request('role') === $r->name ? 'selected' : '' }}>{{ $r->name }}</option>
                    @endforeach
                </select>

                <select name="status" class="form-input" style="height: 38px; padding: 5px 15px; font-size: 13px; width: auto; margin: 0;">
                    <option value="">All Statuses</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                </select>

                <button type="submit" class="btn-signin" style="padding: 0 20px; height: 38px; margin: 0; width: auto; font-size: 12px; background: var(--primary); border-color: var(--primary);">Filter</button>
                @if(request('search') || request('role') || request('status') !== null && request('status') !== '')
                    <a href="{{ route('users.index') }}" class="btn-signin" style="padding: 10px 20px; height: 38px; margin: 0; width: auto; font-size: 12px; background: #718096; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; color: #fff;">Clear</a>
                @endif
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="purity-table">
            <thead>
                <tr>
                    <th>User Profile</th>
                    <th>User Type</th>
                    <th>Assigned Roles</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>
                        <div class="item-profile">
                            <div class="avatar-icon" style="background: rgba(49, 151, 149, 0.1); color: var(--primary);">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                            <div class="item-details">
                                <span class="item-title">{{ $user->name }}</span>
                                <span class="item-subtitle">{{ $user->email }}</span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span style="font-weight: 600; text-transform: capitalize; color: var(--text-color); font-size: 13px;">
                            {{ $user->user_type }}
                        </span>
                    </td>
                    <td>
                        @forelse($user->roles as $role)
                            <span class="badge active" style="background: #EBF8FF; color: #2B6CB0; font-size: 9px; margin-right: 4px;">
                                {{ $role->name }}
                            </span>
                        @empty
                            <span class="badge warning" style="font-size: 9px;">No Role</span>
                        @endforelse
                    </td>
                    <td>
                        <span class="badge {{ $user->is_active ? 'active' : 'danger' }}" style="font-size: 10px;">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td style="text-align: right;">
                        <div style="display: inline-flex; gap: 15px; align-items: center;">
                            <a href="{{ route('users.edit', $user->id) }}" style="color: var(--primary); text-decoration: none; font-weight: 700; font-size: 12px;">Edit</a>
                            
                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="delete-btn" style="background: none; border: none; color: #E53E3E; cursor: pointer; font-weight: 700; font-size: 12px; padding: 0;">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 30px;">No users found in the system.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination Links -->
    <div style="margin-top: 15px;">
        {{ $users->links() }}
    </div>
</div>
@endsection
