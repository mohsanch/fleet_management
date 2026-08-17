@extends('layouts.app')

@section('title', 'Super Admin Dashboard')
@section('breadcrumbs', 'Admin / Dashboard')
@section('page_title', 'Super Admin Dashboard')

@section('content')

{{-- ── KPI STATS ──────────────────────────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:repeat(5,1fr);gap:18px;margin-bottom:28px;">

    <div class="card" style="padding:20px 22px;display:flex;align-items:center;gap:14px;">
        <div style="width:44px;height:44px;border-radius:12px;background:rgba(99,102,241,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i data-lucide="users" style="width:20px;height:20px;color:var(--primary);"></i>
        </div>
        <div>
            <div style="font-size:24px;font-weight:800;color:var(--text-color);line-height:1;">{{ $totalUsers }}</div>
            <div style="font-size:10px;color:var(--text-muted);margin-top:3px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Total Users</div>
        </div>
    </div>

    <div class="card" style="padding:20px 22px;display:flex;align-items:center;gap:14px;">
        <div style="width:44px;height:44px;border-radius:12px;background:rgba(56,161,105,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i data-lucide="user-check" style="width:20px;height:20px;color:#38A169;"></i>
        </div>
        <div>
            <div style="font-size:24px;font-weight:800;color:var(--text-color);line-height:1;">{{ $activeUsers }}</div>
            <div style="font-size:10px;color:var(--text-muted);margin-top:3px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Active Users</div>
        </div>
    </div>

    <div class="card" style="padding:20px 22px;display:flex;align-items:center;gap:14px;">
        <div style="width:44px;height:44px;border-radius:12px;background:rgba(229,62,62,0.10);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i data-lucide="user-x" style="width:20px;height:20px;color:#E53E3E;"></i>
        </div>
        <div>
            <div style="font-size:24px;font-weight:800;color:var(--text-color);line-height:1;">{{ $inactiveUsers }}</div>
            <div style="font-size:10px;color:var(--text-muted);margin-top:3px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Inactive Users</div>
        </div>
    </div>

    <div class="card" style="padding:20px 22px;display:flex;align-items:center;gap:14px;">
        <div style="width:44px;height:44px;border-radius:12px;background:rgba(56,161,105,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i data-lucide="shield-check" style="width:20px;height:20px;color:#38A169;"></i>
        </div>
        <div>
            <div style="font-size:24px;font-weight:800;color:var(--text-color);line-height:1;">{{ $totalRoles }}</div>
            <div style="font-size:10px;color:var(--text-muted);margin-top:3px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Total Roles</div>
        </div>
    </div>

    <div class="card" style="padding:20px 22px;display:flex;align-items:center;gap:14px;">
        <div style="width:44px;height:44px;border-radius:12px;background:rgba(236,153,75,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i data-lucide="key" style="width:20px;height:20px;color:#EC994B;"></i>
        </div>
        <div>
            <div style="font-size:24px;font-weight:800;color:var(--text-color);line-height:1;">{{ $totalPermissions }}</div>
            <div style="font-size:10px;color:var(--text-muted);margin-top:3px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Permissions</div>
        </div>
    </div>

</div>

{{-- ── MAIN GRID ───────────────────────────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start;">

    {{-- ── ACTIVITY FEED ──────────────────────────────────────────────── --}}
    <div class="card table-card">
        <div class="chart-header" style="margin-bottom:18px;align-items:center;">
            <div class="chart-title-block">
                <span class="chart-title">Recent System Activity</span>
                <span class="chart-subtitle">Last 25 actions across the entire system</span>
            </div>
            <a href="{{ route('admin.logs.index') }}" style="font-size:12px;color:var(--primary);font-weight:700;text-decoration:none;white-space:nowrap;">View All →</a>
        </div>

        <div class="table-responsive">
            <table class="purity-table">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>User</th>
                        <th>Description</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentLogs as $log)
                    <tr>
                        <td>
                            @php
                                $action = strtoupper($log->action);
                                $colors = [
                                    'CREATE' => ['bg'=>'rgba(56,161,105,0.12)','color'=>'#38A169'],
                                    'UPDATE' => ['bg'=>'rgba(66,153,225,0.12)','color'=>'#4299E1'],
                                    'DELETE' => ['bg'=>'rgba(229,62,62,0.12)','color'=>'#E53E3E'],
                                    'LOGIN'  => ['bg'=>'rgba(99,102,241,0.12)','color'=>'var(--primary)'],
                                ];
                                $c = $colors[$action] ?? ['bg'=>'rgba(113,128,150,0.12)','color'=>'#718096'];
                            @endphp
                            <span style="font-size:10px;font-weight:700;padding:3px 8px;border-radius:20px;background:{{ $c['bg'] }};color:{{ $c['color'] }};text-transform:uppercase;letter-spacing:0.5px;white-space:nowrap;">
                                {{ $action }}
                            </span>
                        </td>
                        <td>
                            <div style="font-size:12px;font-weight:600;color:var(--text-color);">{{ $log->user?->name ?? 'System' }}</div>
                        </td>
                        <td style="font-size:12px;color:var(--text-muted);max-width:320px;">
                            {{ Str::limit($log->reason ?? $log->description ?? '—', 70) }}
                        </td>
                        <td style="font-size:11px;color:var(--text-muted);white-space:nowrap;">
                            {{ $log->created_at ? \Carbon\Carbon::parse($log->created_at)->diffForHumans() : '—' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align:center;color:var(--text-muted);padding:30px;">No activity recorded yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── RIGHT COLUMN ────────────────────────────────────────────────── --}}
    <div style="display:flex;flex-direction:column;gap:20px;">

        {{-- Quick Links --}}
        <div class="card" style="padding:22px;">
            <div style="font-size:13px;font-weight:700;color:var(--text-color);margin-bottom:16px;display:flex;align-items:center;gap:8px;">
                <i data-lucide="zap" style="width:15px;height:15px;color:var(--primary);"></i>
                Quick Navigation
            </div>
            <div style="display:flex;flex-direction:column;gap:8px;">
                @foreach([
                    ['route'=>'admin.users.index',   'icon'=>'users',      'label'=>'Manage Users',       'desc'=>'Create, edit, toggle users'],
                    ['route'=>'admin.roles.index',   'icon'=>'shield',     'label'=>'Roles & Permissions','desc'=>'Assign permissions to roles'],
                    ['route'=>'admin.permissions.index','icon'=>'key',     'label'=>'Permissions',        'desc'=>'Manage permission keys'],
                    ['route'=>'admin.logs.index',    'icon'=>'file-text',  'label'=>'Activity Logs',      'desc'=>'Full audit trail'],
                ] as $link)
                <a href="{{ route($link['route']) }}" style="display:flex;align-items:center;gap:12px;padding:10px 12px;border-radius:8px;background:var(--bg-light);border:1px solid var(--border-color);text-decoration:none;transition:all 0.15s ease;" onmouseover="this.style.borderColor='var(--primary)';this.style.background='rgba(79,209,197,0.06)'" onmouseout="this.style.borderColor='var(--border-color)';this.style.background='var(--bg-light)'">
                    <div style="width:32px;height:32px;border-radius:8px;background:rgba(99,102,241,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i data-lucide="{{ $link['icon'] }}" style="width:15px;height:15px;color:var(--primary);"></i>
                    </div>
                    <div>
                        <div style="font-size:12px;font-weight:700;color:var(--text-color);">{{ $link['label'] }}</div>
                        <div style="font-size:10px;color:var(--text-muted);">{{ $link['desc'] }}</div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>

        {{-- Users by Role ────────────────────────────────────────────── --}}
        <div class="card" style="padding:22px;">
            <div style="font-size:13px;font-weight:700;color:var(--text-color);margin-bottom:16px;display:flex;align-items:center;gap:8px;">
                <i data-lucide="pie-chart" style="width:15px;height:15px;color:var(--primary);"></i>
                Users by Role
            </div>
            @php
                $colors = ['var(--primary)','#38A169','#EC994B','#E53E3E','#4299E1','#805AD5','#DD6B20'];
                $total  = $usersByRole->sum('count') ?: 1;
            @endphp
            <div style="display:flex;flex-direction:column;gap:10px;">
                @foreach($usersByRole as $i => $item)
                @php $pct = round(($item['count'] / $total) * 100); @endphp
                <div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                        <span style="font-size:12px;font-weight:600;color:var(--text-color);">{{ $item['name'] }}</span>
                        <span style="font-size:12px;color:var(--text-muted);">{{ $item['count'] }} <small>({{ $pct }}%)</small></span>
                    </div>
                    <div style="height:6px;border-radius:6px;background:var(--border-color);overflow:hidden;">
                        <div style="height:100%;width:{{ $pct }}%;background:{{ $colors[$i % count($colors)] }};border-radius:6px;transition:width 0.6s ease;"></div>
                    </div>
                </div>
                @endforeach
                @if($usersByRole->isEmpty())
                <p style="color:var(--text-muted);font-size:12px;text-align:center;">No roles assigned yet.</p>
                @endif
            </div>
        </div>

    </div>
</div>

@endsection
