@extends('layouts.app')

@section('title', 'Role Management')
@section('breadcrumbs', 'Admin / Roles')
@section('page_title', 'Role Management')

@section('content')

{{-- ── STATS ─────────────────────────────────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:18px;margin-bottom:28px;">
    <div class="card" style="padding:18px 20px;display:flex;align-items:center;gap:14px;">
        <div style="width:40px;height:40px;border-radius:10px;background:rgba(99,102,241,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i data-lucide="shield" style="width:18px;height:18px;color:var(--primary);"></i>
        </div>
        <div>
            <div style="font-size:22px;font-weight:800;color:var(--text-color);line-height:1;">{{ $roles->count() }}</div>
            <div style="font-size:10px;color:var(--text-muted);margin-top:2px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Total Roles</div>
        </div>
    </div>
    <div class="card" style="padding:18px 20px;display:flex;align-items:center;gap:14px;">
        <div style="width:40px;height:40px;border-radius:10px;background:rgba(56,161,105,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i data-lucide="users" style="width:18px;height:18px;color:#38A169;"></i>
        </div>
        <div>
            <div style="font-size:22px;font-weight:800;color:var(--text-color);line-height:1;">{{ $roles->sum('users_count') }}</div>
            <div style="font-size:10px;color:var(--text-muted);margin-top:2px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Users Assigned</div>
        </div>
    </div>
    <div class="card" style="padding:18px 20px;display:flex;align-items:center;gap:14px;">
        <div style="width:40px;height:40px;border-radius:10px;background:rgba(236,153,75,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i data-lucide="key" style="width:18px;height:18px;color:#EC994B;"></i>
        </div>
        <div>
            <div style="font-size:22px;font-weight:800;color:var(--text-color);line-height:1;">{{ $roles->sum(fn($r) => $r->permissions->count()) }}</div>
            <div style="font-size:10px;color:var(--text-muted);margin-top:2px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Total Assignments</div>
        </div>
    </div>
</div>

{{-- ── ROLES TABLE ───────────────────────────────────────────────────────── --}}
<div class="card table-card">
    <div class="chart-header" style="margin-bottom:20px;align-items:center;">
        <div class="chart-title-block">
            <span class="chart-title">All Roles</span>
            <span class="chart-subtitle">Create, edit, assign permissions and delete roles</span>
        </div>
        <a href="{{ route('admin.roles.create') }}" class="btn-signin" style="margin:0;width:auto;padding:10px 20px;font-size:13px;text-decoration:none;display:inline-flex;align-items:center;gap:6px;white-space:nowrap;">
            <i data-lucide="plus" style="width:14px;height:14px;"></i> New Role
        </a>
    </div>

    <div class="table-responsive">
        <table class="purity-table">
            <thead>
                <tr>
                    <th>Role Name</th>
                    <th>Users</th>
                    <th>Permissions Assigned</th>
                    <th>Sample Permissions</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($roles as $role)
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:34px;height:34px;border-radius:10px;background:rgba(99,102,241,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i data-lucide="shield" style="width:15px;height:15px;color:var(--primary);"></i>
                            </div>
                            <div>
                                <strong style="font-size:13px;color:var(--text-color);">{{ $role->name }}</strong>
                                <div style="font-size:10px;color:var(--text-muted);">guard: web</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span style="font-size:13px;font-weight:700;color:var(--text-color);">{{ $role->users_count }}</span>
                        <span style="font-size:11px;color:var(--text-muted);"> user{{ $role->users_count != 1 ? 's' : '' }}</span>
                    </td>
                    <td>
                        @php $count = $role->permissions->count(); @endphp
                        <span style="display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:700;background:{{ $count > 0 ? 'rgba(56,161,105,0.12)' : 'rgba(113,128,150,0.1)' }};color:{{ $count > 0 ? '#38A169' : '#718096' }};padding:3px 10px;border-radius:20px;">
                            <i data-lucide="key" style="width:11px;height:11px;"></i>
                            {{ $count }} permission{{ $count != 1 ? 's' : '' }}
                        </span>
                    </td>
                    <td style="max-width:260px;">
                        @if($role->permissions->isNotEmpty())
                        <div style="display:flex;flex-wrap:wrap;gap:4px;">
                            @foreach($role->permissions->take(4) as $perm)
                            <span style="font-size:9px;background:rgba(79,209,197,0.08);color:var(--primary);border:1px solid rgba(79,209,197,0.25);border-radius:10px;padding:2px 7px;">{{ $perm->name }}</span>
                            @endforeach
                            @if($role->permissions->count() > 4)
                            <span style="font-size:9px;color:var(--text-muted);padding:2px 6px;">+{{ $role->permissions->count() - 4 }} more</span>
                            @endif
                        </div>
                        @else
                        <span style="font-size:11px;color:var(--text-muted);">No permissions assigned</span>
                        @endif
                    </td>
                    <td style="text-align:right;">
                        <div style="display:inline-flex;gap:8px;align-items:center;">
                            {{-- Edit --}}
                            <a href="{{ route('admin.roles.edit', $role->id) }}"
                               style="font-size:11px;font-weight:700;color:var(--primary);background:rgba(79,209,197,0.06);border:1px solid rgba(79,209,197,0.3);border-radius:7px;padding:5px 12px;text-decoration:none;display:inline-flex;align-items:center;gap:4px;transition:all 0.15s;"
                               onmouseover="this.style.background='var(--primary)';this.style.color='#fff'"
                               onmouseout="this.style.background='rgba(79,209,197,0.06)';this.style.color='var(--primary)'">
                                <i data-lucide="pencil" style="width:11px;height:11px;"></i> Edit
                            </a>

                            {{-- Delete --}}
                            @if($role->users_count == 0)
                            <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="delete-btn"
                                    style="font-size:11px;font-weight:700;color:#E53E3E;background:rgba(229,62,62,0.06);border:1px solid rgba(229,62,62,0.3);border-radius:7px;padding:5px 12px;cursor:pointer;display:inline-flex;align-items:center;gap:4px;transition:all 0.15s;"
                                    onmouseover="this.style.background='#E53E3E';this.style.color='#fff'"
                                    onmouseout="this.style.background='rgba(229,62,62,0.06)';this.style.color='#E53E3E'">
                                    <i data-lucide="trash-2" style="width:11px;height:11px;"></i> Delete
                                </button>
                            </form>
                            @else
                            <span style="font-size:10px;color:var(--text-muted);padding:5px 8px;background:var(--bg-light);border-radius:7px;border:1px solid var(--border-color);"
                                  title="Cannot delete — role has {{ $role->users_count }} user(s) assigned">
                                <i data-lucide="lock" style="width:10px;height:10px;display:inline;vertical-align:middle;"></i> In Use
                            </span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center;padding:50px;color:var(--text-muted);">
                        <i data-lucide="shield-off" style="width:36px;height:36px;display:block;margin:0 auto 12px;opacity:0.3;"></i>
                        No roles found. <a href="{{ route('admin.roles.create') }}" style="color:var(--primary);font-weight:700;">Create the first role →</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
