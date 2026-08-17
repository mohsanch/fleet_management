@extends('layouts.app')

@section('title', 'Create Role')
@section('breadcrumbs', 'Admin / Roles / Create')
@section('page_title', 'Create New Role')

@section('content')

<div style="display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start;">

    {{-- ── MAIN FORM ─────────────────────────────────────────────────── --}}
    <div class="card">
        <div class="chart-header" style="margin-bottom:24px;">
            <div class="chart-title-block">
                <span class="chart-title">New Role Details</span>
                <span class="chart-subtitle">Give the role a name and assign permissions in one step</span>
            </div>
        </div>

        <form action="{{ route('admin.roles.store') }}" method="POST" id="createRoleForm">
            @csrf

            {{-- Role Name --}}
            <div class="form-group">
                <label for="roleName">Role Name <span style="color:#E53E3E;">*</span></label>
                <input type="text" name="name" id="roleName" class="form-input"
                    placeholder="e.g. Manager, Dispatcher, Accountant…"
                    value="{{ old('name') }}" required autofocus>
                @error('name') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            {{-- Permissions Matrix --}}
            <div style="margin-top:24px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
                    <div>
                        <div style="font-size:13px;font-weight:700;color:var(--text-color);">Assign Permissions</div>
                        <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">Select which permissions this role should have</div>
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
                                    style="accent-color:var(--primary);width:13px;height:13px;"
                                    onchange="toggleGroup('{{ $module }}', this.checked)">
                                Select All
                            </label>
                        </div>
                        <div style="padding:12px 16px;display:flex;flex-wrap:wrap;gap:8px;">
                            @foreach($perms as $perm)
                            <label class="perm-chip chip-{{ $module }}" style="display:flex;align-items:center;gap:5px;font-size:11px;color:var(--text-muted);cursor:pointer;background:var(--bg-light);border:1px solid var(--border-color);border-radius:20px;padding:5px 12px;transition:all 0.15s ease;"
                                onmouseover="this.style.borderColor='var(--primary)'"
                                onmouseout="if(!this.querySelector('input').checked){this.style.borderColor='var(--border-color)'}">
                                <input type="checkbox" name="permissions[]" value="{{ $perm->name }}"
                                    {{ in_array($perm->name, old('permissions', [])) ? 'checked' : '' }}
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
                    Create Role
                </button>
                <a href="{{ route('admin.roles.index') }}" style="padding:12px 24px;border:1px solid var(--border-color);border-radius:10px;background:none;text-decoration:none;font-size:14px;font-weight:600;color:var(--text-muted);display:inline-flex;align-items:center;gap:6px;transition:all 0.15s;" onmouseover="this.style.borderColor='var(--primary)';this.style.color='var(--primary)'" onmouseout="this.style.borderColor='var(--border-color)';this.style.color='var(--text-muted)'">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    {{-- ── SIDEBAR ───────────────────────────────────────────────────── --}}
    <div style="display:flex;flex-direction:column;gap:18px;">

        {{-- Selected count --}}
        <div class="card" style="padding:20px;">
            <div style="font-size:12px;font-weight:700;color:var(--text-color);margin-bottom:14px;display:flex;align-items:center;gap:7px;">
                <i data-lucide="check-circle" style="width:14px;height:14px;color:#38A169;"></i>
                Selected Permissions
            </div>
            <div id="selectedCount" style="font-size:32px;font-weight:800;color:var(--primary);line-height:1;">0</div>
            <div style="font-size:11px;color:var(--text-muted);margin-top:4px;">of {{ $permissions->count() }} total</div>
        </div>

        {{-- Tips --}}
        <div class="card" style="padding:20px;">
            <div style="font-size:12px;font-weight:700;color:var(--text-color);margin-bottom:12px;display:flex;align-items:center;gap:7px;">
                <i data-lucide="info" style="width:14px;height:14px;color:var(--primary);"></i>
                Tips
            </div>
            <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:8px;">
                <li style="font-size:11px;color:var(--text-muted);display:flex;gap:8px;align-items:flex-start;">
                    <i data-lucide="check" style="width:12px;height:12px;color:#38A169;flex-shrink:0;margin-top:1px;"></i>
                    Role names must be unique across the system
                </li>
                <li style="font-size:11px;color:var(--text-muted);display:flex;gap:8px;align-items:flex-start;">
                    <i data-lucide="check" style="width:12px;height:12px;color:#38A169;flex-shrink:0;margin-top:1px;"></i>
                    Permissions can always be changed later from the Edit page
                </li>
                <li style="font-size:11px;color:var(--text-muted);display:flex;gap:8px;align-items:flex-start;">
                    <i data-lucide="check" style="width:12px;height:12px;color:#38A169;flex-shrink:0;margin-top:1px;"></i>
                    Only roles with 0 users assigned can be deleted
                </li>
            </ul>
        </div>

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

// Init chip states on load (for old() values)
document.querySelectorAll('input[name="permissions[]"]').forEach(cb => {
    if (cb.checked) updateChip(cb);
});
updateCount();
</script>
@endsection
