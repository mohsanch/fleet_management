@extends('layouts.app')

@section('title', 'Edit User')
@section('breadcrumbs', 'Users / Edit')
@section('page_title', 'Edit User')

@section('content')
<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="chart-header" style="margin-bottom: 24px;">
        <div class="chart-title-block">
            <span class="chart-title">Modify User Profile</span>
            <span class="chart-subtitle">Update account details, role assignments, and active status</span>
        </div>
        <a href="{{ route('users.index') }}" style="font-size: 11px; font-weight: 700; color: var(--primary); text-decoration: none;">Back to List</a>
    </div>

    <form action="{{ route('users.update', $user->id) }}" method="POST" class="auth-form" style="max-width: 100%; gap: 18px;">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" name="name" id="name" class="form-input" value="{{ old('name', $user->name) }}" required>
            @error('name')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" name="email" id="email" class="form-input" value="{{ old('email', $user->email) }}" required>
            @error('email')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">Password (Leave blank to keep current)</label>
            <div style="display: flex; gap: 10px; width: 100%;">
                <input type="text" name="password" id="password" class="form-input" placeholder="Enter new password or click Generate">
                <button type="button" id="generatePasswordBtn" class="btn-signin" style="width: auto; padding: 10px 20px; margin: 0; background: var(--text-color); font-size: 12px; white-space: nowrap;">Generate</button>
            </div>
            @error('password')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="user_type">User Classification (Type)</label>
            <select name="user_type" id="user_type" class="form-input" style="height: 48px; padding: 10px 20px;" required>
                <option value="super_admin" {{ old('user_type', $user->user_type) === 'super_admin' ? 'selected' : '' }}>Super Admin (Personnel Management)</option>
                <option value="admin" {{ old('user_type', $user->user_type) === 'admin' ? 'selected' : '' }}>Admin (Full control)</option>
                <option value="accountant" {{ old('user_type', $user->user_type) === 'accountant' ? 'selected' : '' }}>Accountant (Financials & Billing)</option>
                <option value="staff" {{ old('user_type', $user->user_type) === 'staff' ? 'selected' : '' }}>Staff (Data entry operations)</option>
            </select>
            @error('user_type')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="role">Assign Access Role (Spatie Permission)</label>
            <select name="role" id="role" class="form-input" style="height: 48px; padding: 10px 20px;" required>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}" {{ old('role', $userRole) === $role->name ? 'selected' : '' }}>
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>
            @error('role')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="branch_id">Assign Branch (leave blank for Global/All)</label>
            <select name="branch_id" id="branch_id" class="form-input" style="height: 48px; padding: 10px 20px;">
                <option value="">Global (All Branches)</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ old('branch_id', $user->branch_id) == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }} ({{ $branch->code }})
                    </option>
                @endforeach
            </select>
            @error('branch_id')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="is_active">Status</label>
            <select name="is_active" id="is_active" class="form-input" style="height: 48px; padding: 10px 20px;" required>
                <option value="1" {{ old('is_active', $user->is_active) ? 'selected' : '' }}>Active (Can login)</option>
                <option value="0" {{ !old('is_active', $user->is_active) ? 'selected' : '' }}>Inactive (Blocked from login)</option>
            </select>
            @error('is_active')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div style="margin-top: 10px;">
            <button type="submit" class="btn-signin" style="margin: 0;">Update User Profile</button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.getElementById('generatePasswordBtn').addEventListener('click', function() {
            const chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%";
            let password = "";
            for (let i = 0; i < 12; i++) {
                password += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            document.getElementById('password').value = password;
        });
    });
</script>
@endsection
