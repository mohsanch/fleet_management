@extends('layouts.app')

@section('title', 'Edit Role — ' . $role->name)
@section('breadcrumbs', 'Admin / Roles / Edit')
@section('page_title', 'Edit Role')

@section('content')

<div style="display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start;">

    {{-- ── MAIN FORM ─────────────────────────────────────────────────── --}}
    <div class="card">
        <div class="chart-header" style="margin-bottom:24px;align-items:flex-start;">
            <div class="chart-title-block">
                <span class="chart-title">Editing: <span style="color:var(--primary);">{{ $role->name }}</span></span>
                <span class="chart-subtitle">Update the role name and its permission set</span>
            </div>
            <div style="display:flex;align-items:center;gap:10px;flex-shrink:0;">
                <span style="font-size:11px;font-weight:700;padding:4px 10px;border-radius:20px;background:rgba(56,161,105,0.12);color:#38A169;">
                    {{ $role->users()->count() }} user{{ $role->users()->count() != 1 ? 's' : '' }} assigned
                </span>
            </div>
        </div>

        <form action="{{ route('admin.roles.update', $role->id) }}" method="POST" id="editRoleForm">
            @csrf @method('PUT')

            {{-- Role Name --}}
            <div class="form-group">
                <label for="roleName">Role Name <span style="color:#E53E3E;">*</span></label>
                <input type="text" name="name" id="roleName" class="form-input"
                    value="{{ old('name', $role->name) }}" required>
                @error('name') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            {{-- Permissions Matrix --}}
            <div style="margin-top:24px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
                    <div>
                        <div style="font-size:13px;font-weight:700;color:var(--text-color);">Permissions</div>
                        <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">Check/uncheck permissions for this role</div>
                    </div>
                    <div style="display:flex;gap:10px;">
                        <button type="button" onclick="selectAll(true)" style="font-size:11px;font-weight:700;color:var(--primary);background:rgba(79,209,197,0.08);border:1px solid rgba(79,209,197,0.25);border-radius:7px;padding:5px 12px;cursor:pointer;">Select All</button>
                        <button type="button" onclick="selectAll(false)" style="font-size:11px;font-weight:700;color:#718096;background:rgba(113,128,150,0.08);border:1px solid rgba(113,128,150,0.25);border-radius:7px;padding:5px 12px;cursor:pointer;">Clear All</button>
                    </div>
                </div>

                @if($groupedPermissions->isEmpty())
                <div style="padding:24px;border:2px dashed var(--border-color);border-radius:10px;text-align:center;color:var(--text-muted);">
                    <i data-lucide="key" style="width:28px;height:28px;display:block;margin:0 auto 8px;opacity:0.35;"></i>
                    <p style="font-size:13px;">No permissions exist yet.</p>
                    <a href="{{ route('admin.permissions.index') }}" style="color:var(--primary);font-size:12px;font-weight:700;">Build permissions first →</a>
                </div>
                @else
                <div style="display:flex;flex-direction:column;gap:12px;">
                    @foreach($groupedPermissions as $module => $perms)
                    @php $allChecked = $perms->every(fn($p) => $role->hasPermissionTo($p->name)); @endphp
                    <div style="border:1px solid var(--border-color);border-radius:10px;overflow:hidden;">
                        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 16px;background:var(--bg-light);border-bottom:1px solid var(--border-color);">
                            <span style="font-size:12px;font-weight:700;color:var(--text-color);text-transform:capitalize;display:flex;align-items:center;gap:6px;">
                                <i data-lucide="layers" style="width:12px;height:12px;color:var(--primary);"></i>
                                {{ ucfirst(str_replace(['-','_'],' ', $module)) }}
                                <span style="font-size:10px;color:var(--text-muted);font-weight:500;">({{ $perms->count() }})</span>
                            </span>
                            <label style="display:flex;align-items:center;gap:5px;cursor:pointer;font-size:11px;color:var(--text-muted);">
                                <input type="checkbox" class="module-all"
                                    data-group="{{ $module }}"
                                    {{ $allChecked ? 'checked' : '' }}
                                    style="accent-color:var(--primary);width:13px;height:13px;"
                                    onchange="toggleGroup('{{ $module }}', this.checked)">
                                Select All
                            </label>
                        </div>
                        <div style="padding:12px 16px;display:flex;flex-wrap:wrap;gap:8px;">
                            @foreach($perms as $perm)
                            @php $isChecked = $role->hasPermissionTo($perm->name); @endphp
                            <label class="perm-chip chip-{{ $module }}"
                                style="display:flex;align-items:center;gap:5px;font-size:11px;cursor:pointer;border-radius:20px;padding:5px 12px;transition:all 0.15s ease;
                                    background:{{ $isChecked ? 'rgba(79,209,197,0.1)' : 'var(--bg-light)' }};
                                    border:1px solid {{ $isChecked ? 'var(--primary)' : 'var(--border-color)' }};
                                    color:{{ $isChecked ? 'var(--text-color)' : 'var(--text-muted)' }};"
                                onmouseover="this.style.borderColor='var(--primary)'"
                                onmouseout="if(!this.querySelector('input').checked){this.style.borderColor='var(--border-color)'}">
                                <input type="checkbox" name="permissions[]" value="{{ $perm->name }}"
                                    {{ $isChecked ? 'checked' : '' }}
                                    style="accent-color:var(--primary);width:12px;height:12px;"
                                    onchange="updateChip(this)">
                                {{ $perm->name }}
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Actions --}}
            <div style="display:flex;gap:12px;margin-top:28px;padding-top:20px;border-top:1px solid var(--border-color);">
                <button type="submit" class="btn-signin" style="margin:0;width:auto;padding:12px 32px;">
                    <i data-lucide="save" style="width:14px;height:14px;display:inline;vertical-align:middle;margin-right:6px;"></i>
                    Save Changes
                </button>
                <a href="{{ route('admin.roles.index') }}" style="padding:12px 24px;border:1px solid var(--border-color);border-radius:10px;background:none;text-decoration:none;font-size:14px;font-weight:600;color:var(--text-muted);display:inline-flex;align-items:center;gap:6px;transition:all 0.15s;" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)'" onmouseout="this.style.borderColor='var(--border-color)';this.style.color='var(--text-muted)'">
                    Cancel
                </a>

                {{-- Danger zone: delete --}}
                @if($role->users()->count() == 0)
                <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" style="display:inline;margin-left:auto;">
                    @csrf @method('DELETE')
                    <button type="submit" class="delete-btn" style="padding:12px 20px;font-size:13px;font-weight:700;color:#E53E3E;background:rgba(229,62,62,0.06);border:1px solid rgba(229,62,62,0.3);border-radius:10px;cursor:pointer;display:inline-flex;align-items:center;gap:6px;transition:all 0.15s;"
                        onmouseover="this.style.background='#E53E3E';this.style.color='#fff'"
                        onmouseout="this.style.background='rgba(229,62,62,0.06)';this.style.color='#E53E3E'">
                        <i data-lucide="trash-2" style="width:13px;height:13px;"></i> Delete Role
                    </button>
                </form>
                @endif
            </div>
        </form>
    </div>

    {{-- ── SIDEBAR ───────────────────────────────────────────────────── --}}
    <div style="display:flex;flex-direction:column;gap:18px;">

        {{-- Role info --}}
        <div class="card" style="padding:20px;">
            <div style="font-size:12px;font-weight:700;color:var(--text-color);margin-bottom:14px;display:flex;align-items:center;gap:7px;">
                <i data-lucide="info" style="width:14px;height:14px;color:var(--primary);"></i>
                Role Info
            </div>
            <div style="display:flex;flex-direction:column;gap:10px;">
                <div style="display:flex;justify-content:space-between;padding:8px 12px;background:var(--bg-light);border-radius:8px;border:1px solid var(--border-color);">
                    <span style="font-size:11px;color:var(--text-muted);">ID</span>
                    <span style="font-size:11px;font-weight:700;color:var(--text-color);">#{{ $role->id }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:8px 12px;background:var(--bg-light);border-radius:8px;border:1px solid var(--border-color);">
                    <span style="font-size:11px;color:var(--text-muted);">Guard</span>
                    <span style="font-size:11px;font-weight:700;color:var(--text-color);">{{ $role->guard_name }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:8px 12px;background:var(--bg-light);border-radius:8px;border:1px solid var(--border-color);">
                    <span style="font-size:11px;color:var(--text-muted);">Users Assigned</span>
                    <span style="font-size:11px;font-weight:700;color:var(--text-color);">{{ $role->users()->count() }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:8px 12px;background:var(--bg-light);border-radius:8px;border:1px solid var(--border-color);">
                    <span style="font-size:11px;color:var(--text-muted);">Permissions</span>
                    <span style="font-size:11px;font-weight:700;color:var(--text-color);" id="selectedCount">{{ $role->permissions->count() }}</span>
                </div>
            </div>
        </div>

        {{-- Current permissions --}}
        @if($role->permissions->isNotEmpty())
        <div class="card" style="padding:20px;">
            <div style="font-size:12px;font-weight:700;color:var(--text-color);margin-bottom:12px;display:flex;align-items:center;gap:7px;">
                <i data-lucide="check-circle" style="width:14px;height:14px;color:#38A169;"></i>
                Currently Active
            </div>
            <div id="activePillsContainer" style="display:flex;flex-wrap:wrap;gap:5px;">
                @foreach($role->permissions as $perm)
                <span class="active-perm-pill" data-perm="{{ $perm->name }}" style="font-size:9px;background:rgba(56,161,105,0.1);color:#38A169;border:1px solid rgba(56,161,105,0.3);border-radius:10px;padding:2px 8px;">{{ $perm->name }}</span>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Nav --}}
        <div class="card" style="padding:20px;">
            <div style="font-size:12px;font-weight:700;color:var(--text-color);margin-bottom:12px;">Admin Sections</div>
            <div style="display:flex;flex-direction:column;gap:7px;">
                @foreach([
                    ['route'=>'admin.dashboard',        'icon'=>'layout-dashboard', 'label'=>'Dashboard'],
                    ['route'=>'admin.roles.index',       'icon'=>'shield',           'label'=>'Role List'],
                    ['route'=>'admin.permissions.index', 'icon'=>'key',              'label'=>'Permission Builder'],
                    ['route'=>'admin.users.index',       'icon'=>'users',            'label'=>'User Management'],
                ] as $link)
                <a href="{{ route($link['route']) }}" style="display:flex;align-items:center;gap:9px;padding:8px 12px;border-radius:8px;background:var(--bg-light);border:1px solid var(--border-color);text-decoration:none;font-size:12px;font-weight:600;color:var(--text-color);transition:all 0.15s;" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='var(--border-color)'">
                    <i data-lucide="{{ $link['icon'] }}" style="width:12px;height:12px;color:var(--primary);flex-shrink:0;"></i>
                    {{ $link['label'] }}
                </a>
                @endforeach
            </div>
        </div>

    </div>
</div>

@endsection

@section('scripts')
<script>
function updateChip(checkbox) {
    const label = checkbox.closest('label');
    if (checkbox.checked) {
        label.style.background = 'rgba(79,209,197,0.1)';
        label.style.borderColor = 'var(--primary)';
        label.style.color = 'var(--text-color)';
    } else {
        label.style.background = 'var(--bg-light)';
        label.style.borderColor = 'var(--border-color)';
        label.style.color = 'var(--text-muted)';
    }
    updateCount();
}

function toggleGroup(group, checked) {
    document.querySelectorAll('.chip-' + group + ' input[type="checkbox"]').forEach(cb => {
        cb.checked = checked;
        updateChip(cb);
    });
}

function selectAll(checked) {
    document.querySelectorAll('input[name="permissions[]"]').forEach(cb => {
        cb.checked = checked;
        updateChip(cb);
    });
    document.querySelectorAll('.module-all').forEach(cb => cb.checked = checked);
}

function updateCount() {
    const count = document.querySelectorAll('input[name="permissions[]"]:checked').length;
    document.getElementById('selectedCount').textContent = count;
}
updateCount();
</script>
@endsection
