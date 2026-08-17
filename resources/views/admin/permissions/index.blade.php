@extends('layouts.app')

@section('title', 'Permission Management')
@section('breadcrumbs', 'Admin / Permissions')
@section('page_title', 'Permission Management')

@section('content')

<div style="display:grid;grid-template-columns:1fr 360px;gap:24px;align-items:start;">

    {{-- ══ LEFT: GROUPED PERMISSIONS ══════════════════════════════════ --}}
    <div class="card table-card">
        <div class="chart-header" style="margin-bottom:20px;">
            <div class="chart-title-block">
                <span class="chart-title">All Permissions</span>
                <span class="chart-subtitle">{{ $allPermissions->count() }} total permissions grouped by module</span>
            </div>
        </div>

        @if($grouped->isEmpty())
        <div style="text-align:center;padding:60px 0;color:var(--text-muted);">
            <i data-lucide="key" style="width:40px;height:40px;margin-bottom:12px;opacity:0.3;"></i>
            <p style="font-size:14px;">No permissions created yet. Use the panel on the right to generate them.</p>
        </div>
        @else
        <div style="display:flex;flex-direction:column;gap:16px;">
            @foreach($grouped as $module => $perms)
            <div style="border:1px solid var(--border-color);border-radius:12px;overflow:hidden;">
                <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 18px;background:var(--bg-light);border-bottom:1px solid var(--border-color);">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:28px;height:28px;border-radius:8px;background:rgba(99,102,241,0.12);display:flex;align-items:center;justify-content:center;">
                            <i data-lucide="layers" style="width:13px;height:13px;color:var(--primary);"></i>
                        </div>
                        <span style="font-size:13px;font-weight:700;color:var(--text-color);text-transform:capitalize;">{{ ucfirst(str_replace(['-','_'],' ', $module)) }}</span>
                        <span style="font-size:10px;background:rgba(99,102,241,0.1);color:var(--primary);border-radius:10px;padding:2px 8px;">{{ $perms->count() }} perms</span>
                    </div>
                </div>
                <div style="padding:14px 18px;display:flex;flex-wrap:wrap;gap:8px;">
                    @foreach($perms as $perm)
                    @php
                        // Count how many roles have this permission
                        $roleCount = $roles->filter(fn($r) => $r->hasPermissionTo($perm->name))->count();
                    @endphp
                    <div style="display:flex;align-items:center;gap:0;background:var(--bg-light);border:1px solid var(--border-color);border-radius:20px;overflow:hidden;">
                        <span style="font-size:11px;color:var(--text-color);font-weight:600;padding:5px 12px;">
                            {{ $perm->name }}
                        </span>
                        @if($roleCount > 0)
                        <span style="font-size:10px;background:rgba(56,161,105,0.12);color:#38A169;padding:5px 8px;font-weight:700;border-left:1px solid var(--border-color);" title="{{ $roleCount }} role(s) use this permission">
                            {{ $roleCount }}R
                        </span>
                        @endif
                        <form action="{{ route('admin.permissions.destroy', $perm->id) }}" method="POST" style="display:inline;line-height:1;">
                            @csrf @method('DELETE')
                            <input type="hidden" name="redirect_to" value="permissions">
                            <button type="submit" class="delete-btn" style="background:none;border:none;border-left:1px solid var(--border-color);padding:5px 9px;cursor:pointer;color:var(--text-muted);display:flex;align-items:center;transition:all 0.15s;" title="Delete {{ $perm->name }}" onmouseover="this.style.color='#E53E3E'" onmouseout="this.style.color='var(--text-muted)'">
                                <i data-lucide="x" style="width:11px;height:11px;"></i>
                            </button>
                        </form>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- ══ RIGHT: TOOLS ════════════════════════════════════════════════ --}}
    <div style="display:flex;flex-direction:column;gap:20px;">

        {{-- Bulk Generate --}}
        <div class="card" style="padding:22px;">
            <div style="font-size:13px;font-weight:700;color:var(--text-color);margin-bottom:4px;display:flex;align-items:center;gap:8px;">
                <i data-lucide="zap" style="width:15px;height:15px;color:var(--primary);"></i>
                Bulk Generate
            </div>
            <p style="font-size:11px;color:var(--text-muted);margin-bottom:16px;">Generate multiple permissions for a module at once (e.g. users.view, users.create…)</p>

            <form action="{{ route('admin.permissions.bulk') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>Module Name</label>
                    <input type="text" name="module" class="form-input" placeholder="e.g. users, fleet, reports" style="height:44px;" required>
                    @error('module') <span class="form-error">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label style="margin-bottom:10px;display:block;">Select Actions</label>
                    <div style="display:flex;flex-wrap:wrap;gap:8px;">
                        @foreach(['view','create','edit','delete','approve','export'] as $action)
                        <label style="display:flex;align-items:center;gap:6px;background:var(--bg-light);border:1px solid var(--border-color);border-radius:20px;padding:6px 14px;cursor:pointer;font-size:12px;color:var(--text-muted);transition:all 0.15s;" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="if(!this.querySelector('input').checked){this.style.borderColor='var(--border-color)'}">
                            <input type="checkbox" name="actions[]" value="{{ $action }}" style="accent-color:var(--primary);width:12px;height:12px;" onchange="updateActionChip(this)" checked>
                            .{{ $action }}
                        </label>
                        @endforeach
                    </div>
                    @error('actions') <span class="form-error">{{ $message }}</span> @enderror
                </div>

                <div style="background:var(--bg-light);border:1px solid var(--border-color);border-radius:8px;padding:10px 14px;margin-bottom:14px;">
                    <div style="font-size:10px;color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px;">Preview</div>
                    <div id="perm-preview" style="font-size:11px;color:var(--text-muted);font-family:monospace;">module.view, module.create, module.edit, module.delete, module.approve, module.export</div>
                </div>

                <button type="submit" class="btn-signin" style="margin:0;width:100%;padding:12px;">
                    <i data-lucide="zap" style="width:13px;height:13px;display:inline;vertical-align:middle;margin-right:6px;"></i>
                    Generate Permissions
                </button>
            </form>
        </div>

        {{-- Single Permission --}}
        <div class="card" style="padding:22px;">
            <div style="font-size:13px;font-weight:700;color:var(--text-color);margin-bottom:4px;display:flex;align-items:center;gap:8px;">
                <i data-lucide="plus-circle" style="width:15px;height:15px;color:var(--primary);"></i>
                Custom Permission
            </div>
            <p style="font-size:11px;color:var(--text-muted);margin-bottom:16px;">Create a single custom permission key</p>
            <form action="{{ route('admin.permissions.store') }}" method="POST">
                @csrf
                <input type="hidden" name="redirect_to" value="permissions">
                <div class="form-group">
                    <label>Permission Key</label>
                    <input type="text" name="name" class="form-input" placeholder="e.g. manage-settings, export-pdf" style="height:44px;" required>
                    @error('name') <span class="form-error">{{ $message }}</span> @enderror
                </div>
                <button type="submit" class="btn-signin" style="margin:0;width:100%;padding:12px;">
                    <i data-lucide="plus" style="width:13px;height:13px;display:inline;vertical-align:middle;margin-right:6px;"></i>
                    Add Permission
                </button>
            </form>
        </div>

        {{-- Navigation --}}
        <div class="card" style="padding:20px;">
            <div style="font-size:12px;font-weight:700;color:var(--text-color);margin-bottom:12px;">Admin Sections</div>
            <div style="display:flex;flex-direction:column;gap:8px;">
                @foreach([
                    ['route'=>'admin.dashboard',    'icon'=>'layout-dashboard', 'label'=>'Dashboard'],
                    ['route'=>'admin.roles.index',  'icon'=>'shield',           'label'=>'Roles Matrix'],
                    ['route'=>'admin.users.index',  'icon'=>'users',            'label'=>'User Management'],
                    ['route'=>'admin.logs.index',   'icon'=>'file-text',        'label'=>'Activity Logs'],
                ] as $link)
                <a href="{{ route($link['route']) }}" style="display:flex;align-items:center;gap:10px;padding:9px 12px;border-radius:8px;background:var(--bg-light);border:1px solid var(--border-color);text-decoration:none;font-size:12px;font-weight:600;color:var(--text-color);transition:all 0.15s;" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='var(--border-color)'">
                    <i data-lucide="{{ $link['icon'] }}" style="width:13px;height:13px;color:var(--primary);flex-shrink:0;"></i>
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
function updateActionChip(cb) {
    const label = cb.closest('label');
    if (cb.checked) {
        label.style.borderColor = 'var(--primary)';
        label.style.color = 'var(--text-color)';
        label.style.background = 'rgba(79,209,197,0.1)';
    } else {
        label.style.borderColor = 'var(--border-color)';
        label.style.color = 'var(--text-muted)';
        label.style.background = 'var(--bg-light)';
    }
    updatePreview();
}

function updatePreview() {
    const module = document.querySelector('input[name="module"]')?.value || 'module';
    const actions = [...document.querySelectorAll('input[name="actions[]"]:checked')].map(cb => cb.value);
    const preview = actions.length ? actions.map(a => module + '.' + a).join(', ') : '(select actions above)';
    document.getElementById('perm-preview').textContent = preview;
}

document.querySelector('input[name="module"]')?.addEventListener('input', updatePreview);
document.querySelectorAll('input[name="actions[]"]').forEach(cb => cb.addEventListener('change', updatePreview));

// Init chip states on load
document.querySelectorAll('input[name="actions[]"]').forEach(cb => {
    const label = cb.closest('label');
    if (cb.checked) {
        label.style.borderColor = 'var(--primary)';
        label.style.color = 'var(--text-color)';
        label.style.background = 'rgba(79,209,197,0.1)';
    }
});
updatePreview();
</script>
@endsection
