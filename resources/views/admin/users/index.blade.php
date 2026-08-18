@extends('layouts.app')

@section('title', 'User Management')
@section('breadcrumbs', 'Admin / Users')
@section('page_title', 'User Management')

@section('content')

{{-- ── STATS ROW ─────────────────────────────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:18px;margin-bottom:28px;">
    @php
        $allUsersCol = \App\Models\User::all();
        $activeCount   = $allUsersCol->where('is_active', true)->count();
        $inactiveCount = $allUsersCol->where('is_active', false)->count();
        $totalCount    = $allUsersCol->count();
    @endphp
    <div class="card" style="padding:18px 20px;display:flex;align-items:center;gap:14px;">
        <div style="width:40px;height:40px;border-radius:10px;background:rgba(99,102,241,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i data-lucide="users" style="width:18px;height:18px;color:var(--primary);"></i>
        </div>
        <div>
            <div style="font-size:22px;font-weight:800;color:var(--text-color);line-height:1;">{{ $totalCount }}</div>
            <div style="font-size:10px;color:var(--text-muted);margin-top:2px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Total Users</div>
        </div>
    </div>
    <div class="card" style="padding:18px 20px;display:flex;align-items:center;gap:14px;">
        <div style="width:40px;height:40px;border-radius:10px;background:rgba(56,161,105,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i data-lucide="user-check" style="width:18px;height:18px;color:#38A169;"></i>
        </div>
        <div>
            <div style="font-size:22px;font-weight:800;color:var(--text-color);line-height:1;">{{ $activeCount }}</div>
            <div style="font-size:10px;color:var(--text-muted);margin-top:2px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Active</div>
        </div>
    </div>
    <div class="card" style="padding:18px 20px;display:flex;align-items:center;gap:14px;">
        <div style="width:40px;height:40px;border-radius:10px;background:rgba(229,62,62,0.10);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i data-lucide="user-x" style="width:18px;height:18px;color:#E53E3E;"></i>
        </div>
        <div>
            <div style="font-size:22px;font-weight:800;color:var(--text-color);line-height:1;">{{ $inactiveCount }}</div>
            <div style="font-size:10px;color:var(--text-muted);margin-top:2px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Inactive</div>
        </div>
    </div>
    <div class="card" style="padding:18px 20px;display:flex;align-items:center;gap:14px;">
        <div style="width:40px;height:40px;border-radius:10px;background:rgba(56,161,105,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i data-lucide="shield" style="width:18px;height:18px;color:#38A169;"></i>
        </div>
        <div>
            <div style="font-size:22px;font-weight:800;color:var(--text-color);line-height:1;">{{ $roles->count() }}</div>
            <div style="font-size:10px;color:var(--text-muted);margin-top:2px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Roles</div>
        </div>
    </div>
</div>

{{-- ── MAIN TABLE ────────────────────────────────────────────────────────── --}}
<div class="card table-card">
    {{-- Header row --}}
    <div class="chart-header" style="margin-bottom:18px;align-items:center;">
        <div class="chart-title-block">
            <span class="chart-title">All System Users</span>
            <span class="chart-subtitle">Manage user accounts, roles, and access privileges</span>
        </div>
        <a href="{{ route('users.create') }}" class="btn-signin" style="margin:0;width:auto;padding:10px 20px;font-size:13px;text-decoration:none;display:inline-flex;align-items:center;gap:6px;white-space:nowrap;">
            <i data-lucide="user-plus" style="width:14px;height:14px;"></i> New User
        </a>
    </div>

    {{-- Filters --}}
    <form action="{{ route('admin.users.index') }}" method="GET" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:18px;padding:14px 16px;background:var(--bg-light);border:1px solid var(--border-color);border-radius:10px;">
        <input type="text" name="search" class="form-input" placeholder="Search name or email…" value="{{ request('search') }}" style="height:36px;padding:5px 14px;font-size:13px;max-width:220px;margin:0;">
        <select name="role" class="form-input" style="height:36px;padding:5px 14px;font-size:13px;width:auto;margin:0;">
            <option value="">All Roles</option>
            @foreach($roles as $role)
            <option value="{{ $role->name }}" {{ request('role') === $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
            @endforeach
        </select>
        <select name="status" class="form-input" style="height:36px;padding:5px 14px;font-size:13px;width:auto;margin:0;">
            <option value="">All Statuses</option>
            <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
            <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
        </select>
        <button type="submit" class="btn-signin" style="height:36px;width:auto;padding:0 16px;font-size:12px;margin:0;">Filter</button>
        @if(request('search') || request('role') || request('status') !== null && request('status') !== '')
        <a href="{{ route('admin.users.index') }}" class="btn-signin" style="height:36px;padding:0 16px;font-size:12px;background:#718096;display:inline-flex;align-items:center;text-decoration:none;color:#fff;margin:0;">Clear</a>
        @endif
    </form>

    {{-- Table --}}
    <div class="table-responsive">
        <table class="purity-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Role</th>
                    <th>Branch</th>
                    <th>Permissions</th>
                    <th>Status</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:12px;">
                            <div style="width:36px;height:36px;border-radius:50%;background:var(--primary);color:#fff;font-size:14px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <strong style="color:var(--text-color);font-size:13px;">{{ $user->name }}</strong>
                                <div style="font-size:11px;color:var(--text-muted);">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($user->roles->first())
                        <span style="background:rgba(99,102,241,0.12);color:var(--primary);font-size:10px;font-weight:700;padding:3px 10px;border-radius:20px;text-transform:uppercase;letter-spacing:0.5px;">
                            {{ $user->roles->first()->name }}
                        </span>
                        @else
                        <span style="color:var(--text-muted);font-size:11px;">No role</span>
                        @endif
                    </td>
                    <td>
                        @if($user->branch)
                            <span class="badge info" style="font-size: 10px;">
                                {{ $user->branch->name }}
                            </span>
                        @else
                            <span class="badge" style="background: #EDF2F7; color: #4A5568; font-size: 10px;">
                                Global (All)
                            </span>
                        @endif
                    </td>
                    <td>
                        @php $permsCount = $user->getAllPermissions()->count(); @endphp
                        <span style="font-size:12px;color:var(--text-muted);">
                            {{ $permsCount }} permission{{ $permsCount !== 1 ? 's' : '' }}
                            @if($permsCount > 0)
                            <button type="button" onclick="togglePermList({{ $user->id }})" style="background:none;border:none;cursor:pointer;color:var(--primary);font-size:11px;font-weight:700;padding:0;margin-left:4px;">view</button>
                            @endif
                        </span>
                        @if($permsCount > 0)
                        <div id="perm-list-{{ $user->id }}" style="display:none;margin-top:6px;display:none;">
                            <div style="display:flex;flex-wrap:wrap;gap:4px;max-width:260px;">
                                @foreach($user->getAllPermissions() as $perm)
                                <span style="font-size:9px;background:rgba(79,209,197,0.1);color:var(--primary);border:1px solid rgba(79,209,197,0.3);border-radius:10px;padding:2px 7px;">{{ $perm->name }}</span>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </td>
                    <td>
                        @if($user->is_active)
                        <span style="font-size:10px;font-weight:700;background:rgba(56,161,105,0.12);color:#38A169;padding:3px 10px;border-radius:20px;">Active</span>
                        @else
                        <span style="font-size:10px;font-weight:700;background:rgba(229,62,62,0.12);color:#E53E3E;padding:3px 10px;border-radius:20px;">Inactive</span>
                        @endif
                    </td>
                    <td style="text-align:right;">
                        <div style="display:inline-flex;gap:8px;align-items:center;flex-wrap:wrap;justify-content:flex-end;">
                            {{-- Edit user --}}
                            <a href="{{ route('users.edit', $user->id) }}" style="font-size:11px;font-weight:700;color:var(--primary);text-decoration:none;padding:5px 10px;border:1px solid rgba(79,209,197,0.3);border-radius:7px;background:rgba(79,209,197,0.06);transition:all 0.15s;" onmouseover="this.style.background='var(--primary)';this.style.color='#fff'" onmouseout="this.style.background='rgba(79,209,197,0.06)';this.style.color='var(--primary)'">
                                <i data-lucide="pencil" style="width:11px;height:11px;display:inline;vertical-align:middle;"></i> Edit
                            </a>

                            {{-- Toggle Active --}}
                            @if($user->id !== auth()->id())
                            <form action="{{ route('admin.users.toggle', $user->id) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" style="font-size:11px;font-weight:700;padding:5px 10px;border-radius:7px;cursor:pointer;transition:all 0.15s;border:1px solid;{{ $user->is_active ? 'color:#E53E3E;background:rgba(229,62,62,0.06);border-color:rgba(229,62,62,0.3);' : 'color:#38A169;background:rgba(56,161,105,0.06);border-color:rgba(56,161,105,0.3);' }}">
                                    <i data-lucide="{{ $user->is_active ? 'user-x' : 'user-check' }}" style="width:11px;height:11px;display:inline;vertical-align:middle;"></i>
                                    {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>

                            {{-- Direct Permissions --}}
                            <button type="button" onclick="openDirectPermissionsModal({{ $user->id }}, '{{ addslashes($user->name) }}', {{ json_encode($user->permissions->pluck('name')) }}, {{ json_encode($user->getPermissionsViaRoles()->pluck('name')) }})" style="font-size:11px;font-weight:700;color:#3182ce;background:rgba(49,130,206,0.06);border:1px solid rgba(49,130,206,0.3);border-radius:7px;padding:5px 10px;cursor:pointer;transition:all 0.15s;" onmouseover="this.style.background='#3182ce';this.style.color='#fff'" onmouseout="this.style.background='rgba(49,130,206,0.06)';this.style.color='#3182ce'">
                                <i data-lucide="shield-alert" style="width:11px;height:11px;display:inline;vertical-align:middle;"></i> Direct Perms
                            </button>

                            {{-- Reset Password --}}
                            <button type="button" onclick="openResetModal({{ $user->id }}, '{{ addslashes($user->name) }}')" style="font-size:11px;font-weight:700;color:#718096;background:rgba(113,128,150,0.06);border:1px solid rgba(113,128,150,0.3);border-radius:7px;padding:5px 10px;cursor:pointer;transition:all 0.15s;" onmouseover="this.style.background='#718096';this.style.color='#fff'" onmouseout="this.style.background='rgba(113,128,150,0.06)';this.style.color='#718096'">
                                <i data-lucide="key" style="width:11px;height:11px;display:inline;vertical-align:middle;"></i> Reset Pwd
                            </button>

                            {{-- Delete --}}
                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="delete-btn" style="font-size:11px;font-weight:700;color:#E53E3E;background:rgba(229,62,62,0.06);border:1px solid rgba(229,62,62,0.3);border-radius:7px;padding:5px 10px;cursor:pointer;">
                                    <i data-lucide="trash-2" style="width:11px;height:11px;display:inline;vertical-align:middle;"></i> Delete
                                </button>
                            </form>
                            @else
                            <span style="font-size:11px;color:var(--text-muted);padding:5px 10px;">(You)</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center;color:var(--text-muted);padding:40px;">No users found matching your filters.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:15px;">{{ $users->links() }}</div>
</div>

{{-- ── PASSWORD RESET MODAL ─────────────────────────────────────────────── --}}
<div id="resetPasswordModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:16px;padding:30px;width:100%;max-width:400px;margin:20px;box-shadow:0 20px 60px rgba(0,0,0,0.2);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
            <div>
                <div style="font-size:16px;font-weight:700;color:var(--text-color);">Reset Password</div>
                <div id="resetModalSubtitle" style="font-size:12px;color:var(--text-muted);margin-top:2px;"></div>
            </div>
            <button type="button" onclick="closeResetModal()" style="background:none;border:none;cursor:pointer;color:var(--text-muted);">
                <i data-lucide="x" style="width:18px;height:18px;"></i>
            </button>
        </div>
        <form id="resetPasswordForm" method="POST">
            @csrf
            <div class="form-group">
                <label>New Password</label>
                <input type="text" name="new_password" id="resetNewPassword" class="form-input" placeholder="Min. 8 characters" required minlength="8" style="height:44px;">
            </div>
            <div style="display:flex;gap:10px;margin-top:6px;">
                <button type="submit" class="btn-signin" style="margin:0;flex:1;padding:12px;">Reset Password</button>
                <button type="button" onclick="closeResetModal()" style="flex:1;padding:12px;border:1px solid var(--border-color);background:none;border-radius:10px;cursor:pointer;font-size:14px;color:var(--text-muted);">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- ── DIRECT PERMISSIONS MODAL ────────────────────────────────────────── --}}
<div id="directPermissionsModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9998;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:16px;padding:30px;width:100%;max-width:680px;margin:20px;box-shadow:0 20px 60px rgba(0,0,0,0.2);display:flex;flex-direction:column;max-height:85vh;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:15px;flex-shrink:0;">
            <div>
                <div style="font-size:16px;font-weight:700;color:var(--text-color);">User Direct Permissions</div>
                <div id="directModalSubtitle" style="font-size:12px;color:var(--text-muted);margin-top:2px;"></div>
            </div>
            <button type="button" onclick="closeDirectPermissionsModal()" style="background:none;border:none;cursor:pointer;color:var(--text-muted);">
                <i data-lucide="x" style="width:18px;height:18px;"></i>
            </button>
        </div>

        <form id="directPermissionsForm" method="POST" style="display:flex;flex-direction:column;overflow:hidden;flex:1;margin:0;">
            @csrf
            
            {{-- Scrollable Permissions Matrix --}}
            <div style="overflow-y:auto;flex:1;padding-right:8px;margin-bottom:20px;">
                <div style="background:rgba(99,102,241,0.06);border:1px solid rgba(99,102,241,0.2);border-radius:10px;padding:12px 14px;margin-bottom:16px;font-size:11px;color:var(--text-muted);">
                    <strong>Note:</strong> Direct permissions are assigned directly to the user. They will have these permissions *in addition* to any permissions inherited from their role.
                </div>

                <div style="display:flex;flex-direction:column;gap:12px;">
                    @foreach($groupedPermissions as $module => $perms)
                    <div style="border:1px solid var(--border-color);border-radius:10px;overflow:hidden;">
                        <div style="padding:8px 14px;background:var(--bg-light);border-bottom:1px solid var(--border-color);font-size:11px;font-weight:700;color:var(--text-color);text-transform:capitalize;display:flex;align-items:center;gap:6px;">
                            <i data-lucide="layers" style="width:11px;height:11px;color:var(--primary);"></i>
                            {{ ucfirst(str_replace(['-','_'], ' ', $module)) }}
                        </div>
                        <div style="padding:10px 14px;display:flex;flex-wrap:wrap;gap:6px;">
                            @foreach($perms as $perm)
                            <label class="direct-perm-label" id="label-direct-{{ str_replace('.', '_', $perm->name) }}"
                                style="display:flex;align-items:center;gap:5px;font-size:10px;color:var(--text-muted);cursor:pointer;background:var(--bg-light);border:1px solid var(--border-color);border-radius:20px;padding:4px 10px;transition:all 0.15s ease;">
                                <input type="checkbox" name="permissions[]" value="{{ $perm->name }}"
                                    id="cb-direct-{{ str_replace('.', '_', $perm->name) }}"
                                    style="accent-color:var(--primary);width:11px;height:11px;"
                                    onchange="updateDirectChip(this)">
                                <span>{{ $perm->name }}</span>
                                <span class="role-badge" id="badge-direct-{{ str_replace('.', '_', $perm->name) }}" style="display:none; font-size: 8px; color: #3182ce; background: rgba(49, 130, 206, 0.1); padding: 1px 5px; border-radius: 10px; margin-left: 4px; font-weight: bold; white-space: nowrap;">via role</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div style="display:flex;gap:10px;flex-shrink:0;padding-top:15px;border-top:1px solid var(--border-color);">
                <button type="submit" class="btn-signin" style="margin:0;flex:1;padding:12px;">Save Permissions</button>
                <button type="button" onclick="closeDirectPermissionsModal()" style="flex:1;padding:12px;border:1px solid var(--border-color);background:none;border-radius:10px;cursor:pointer;font-size:14px;color:var(--text-muted);">Cancel</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
function openResetModal(userId, userName) {
    document.getElementById('resetPasswordModal').style.display = 'flex';
    document.getElementById('resetModalSubtitle').textContent = 'For user: ' + userName;
    document.getElementById('resetPasswordForm').action = '/admin/users/' + userId + '/reset-password';
    document.getElementById('resetNewPassword').value = '';
}
function closeResetModal() {
    document.getElementById('resetPasswordModal').style.display = 'none';
}
function togglePermList(userId) {
    const el = document.getElementById('perm-list-' + userId);
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}

function openDirectPermissionsModal(userId, userName, userDirectPerms, userRolePerms) {
    document.getElementById('directPermissionsModal').style.display = 'flex';
    document.getElementById('directModalSubtitle').textContent = 'Assign direct/extra permissions to: ' + userName;
    document.getElementById('directPermissionsForm').action = '/admin/users/' + userId + '/direct-permissions';
    
    // Clear all checkboxes first
    document.querySelectorAll('input[id^="cb-direct-"]').forEach(cb => {
        cb.checked = false;
        cb.disabled = false;
        updateDirectChip(cb);
    });

    // Hide all role badges
    document.querySelectorAll('.role-badge').forEach(badge => {
        badge.style.display = 'none';
    });

    // Pre-check and disable checkboxes that user has via role
    userRolePerms.forEach(permName => {
        const idSafeName = permName.replace(/\./g, '_');
        const cb = document.getElementById('cb-direct-' + idSafeName);
        const badge = document.getElementById('badge-direct-' + idSafeName);
        if (cb) {
            cb.checked = true;
            cb.disabled = true; // cannot remove from here, must edit role
            if (badge) badge.style.display = 'inline-block';
            
            // Apply distinct role permission styling
            const label = cb.closest('label');
            label.style.background = 'rgba(66, 153, 225, 0.08)';
            label.style.borderColor = '#4299e1';
            label.style.color = '#2b6cb0';
        }
    });

    // Pre-check and enable checkboxes that user has directly
    userDirectPerms.forEach(permName => {
        const idSafeName = permName.replace(/\./g, '_');
        const cb = document.getElementById('cb-direct-' + idSafeName);
        if (cb) {
            cb.checked = true;
            cb.disabled = false; // direct can be toggled
            
            // Apply distinct direct permission styling
            const label = cb.closest('label');
            label.style.background = 'rgba(79, 209, 197, 0.1)';
            label.style.borderColor = 'var(--primary)';
            label.style.color = 'var(--text-color)';
        }
    });
}

function closeDirectPermissionsModal() {
    document.getElementById('directPermissionsModal').style.display = 'none';
}

function updateDirectChip(checkbox) {
    const label = checkbox.closest('label');
    if (checkbox.disabled) return; // keep role styling
    
    if (checkbox.checked) {
        label.style.background = 'rgba(79,209,197,0.1)';
        label.style.borderColor = 'var(--primary)';
        label.style.color = 'var(--text-color)';
    } else {
        label.style.background = 'var(--bg-light)';
        label.style.borderColor = 'var(--border-color)';
        label.style.color = 'var(--text-muted)';
    }
}
</script>
@endsection
