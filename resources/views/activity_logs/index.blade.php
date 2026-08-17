@extends('layouts.app')

@section('title', $isAdmin ? 'System Audit Trail' : 'My Activity Log')
@section('breadcrumbs', 'Activity Logs')
@section('page_title', $isAdmin ? 'System Activity Logs' : 'My Activity Log')

@section('content')
<div class="card table-card">

    <div class="chart-header" style="align-items:center;margin-bottom:20px;">
        <div class="chart-title-block">
            @if($isAdmin)
                <span class="chart-title">System Audit Trail</span>
                <span class="chart-subtitle">All actions performed by every user across the system</span>
            @else
                <span class="chart-title">My Activity Log</span>
                <span class="chart-subtitle">A record of all actions you have performed in this system</span>
            @endif
        </div>
        @if($isAdmin)
        <span style="font-size:11px;font-weight:700;background:rgba(99,102,241,0.12);color:var(--primary);padding:4px 12px;border-radius:20px;white-space:nowrap;">
            <i data-lucide="shield-check" style="width:11px;height:11px;display:inline;vertical-align:middle;margin-right:3px;"></i>
            All Users — Full View
        </span>
        @else
        <span style="font-size:11px;font-weight:700;background:rgba(56,161,105,0.12);color:#38A169;padding:4px 12px;border-radius:20px;white-space:nowrap;">
            <i data-lucide="user" style="width:11px;height:11px;display:inline;vertical-align:middle;margin-right:3px;"></i>
            Your Logs Only
        </span>
        @endif
    </div>

    {{-- Filters --}}
    <form action="{{ route('activity-logs.index') }}" method="GET"
          style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:18px;padding:12px 16px;background:var(--bg-light);border:1px solid var(--border-color);border-radius:10px;align-items:center;">
        <input type="text" name="search" class="form-input"
               placeholder="{{ $isAdmin ? 'Search action, user, or model…' : 'Search action or description…' }}"
               value="{{ request('search') }}"
               style="height:36px;padding:5px 14px;font-size:13px;flex:1;min-width:160px;max-width:260px;margin:0;">
        <select name="action" class="form-input" style="height:36px;padding:5px 14px;font-size:13px;width:auto;margin:0;">
            <option value="">All Actions</option>
            @foreach(['CREATE','UPDATE','DELETE','LOGIN'] as $act)
            <option value="{{ $act }}" {{ request('action') === $act ? 'selected' : '' }}>{{ $act }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-signin" style="height:36px;width:auto;padding:0 16px;font-size:12px;margin:0;">
            <i data-lucide="filter" style="width:12px;height:12px;display:inline;vertical-align:middle;margin-right:3px;"></i>Filter
        </button>
        @if(request('search') || request('action'))
        <a href="{{ route('activity-logs.index') }}" class="btn-signin"
           style="height:36px;padding:0 14px;font-size:12px;background:#718096;display:inline-flex;align-items:center;text-decoration:none;color:#fff;margin:0;">Clear</a>
        @endif
    </form>

    <div class="table-responsive">
        <table class="purity-table">
            <thead>
                <tr>
                    @if($isAdmin)<th>User</th>@endif
                    <th>Action</th>
                    <th>Record Type</th>
                    <th>Description</th>
                    <th>Date & Time</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    @if($isAdmin)
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:30px;height:30px;border-radius:50%;background:var(--primary);color:#fff;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                {{ strtoupper(substr($log->user?->name ?? 'SY', 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-size:12px;font-weight:600;color:var(--text-color);">{{ $log->user?->name ?? 'System' }}</div>
                                <div style="font-size:10px;color:var(--text-muted);">{{ $log->user?->email ?? '' }}</div>
                            </div>
                        </div>
                    </td>
                    @endif
                    <td>
                        @php
                            $action = strtoupper($log->action ?? '');
                            $cfg = match(true) {
                                str_contains($action,'CREATE') => ['bg'=>'rgba(56,161,105,0.12)','color'=>'#38A169'],
                                str_contains($action,'UPDATE') => ['bg'=>'rgba(66,153,225,0.12)','color'=>'#4299E1'],
                                str_contains($action,'DELETE') => ['bg'=>'rgba(229,62,62,0.12)','color'=>'#E53E3E'],
                                str_contains($action,'LOGIN')  => ['bg'=>'rgba(99,102,241,0.12)','color'=>'var(--primary)'],
                                default                        => ['bg'=>'rgba(113,128,150,0.12)','color'=>'#718096'],
                            };
                        @endphp
                        <span style="font-size:10px;font-weight:700;padding:3px 9px;border-radius:20px;background:{{ $cfg['bg'] }};color:{{ $cfg['color'] }};text-transform:uppercase;letter-spacing:0.5px;white-space:nowrap;">
                            {{ $action }}
                        </span>
                    </td>
                    <td>
                        @if($log->model_type)
                        <span style="font-size:11px;font-weight:600;color:var(--text-color);background:var(--bg-light);border:1px solid var(--border-color);border-radius:6px;padding:2px 8px;">
                            {{ class_basename($log->model_type) }}
                            @if($log->model_id)<span style="color:var(--text-muted);"> #{{ $log->model_id }}</span>@endif
                        </span>
                        @else
                        <span style="color:var(--text-muted);font-size:11px;">—</span>
                        @endif
                    </td>
                    <td style="font-size:12px;color:var(--text-muted);max-width:340px;">
                        {{ $log->reason ?? $log->description ?? '—' }}
                    </td>
                    <td style="white-space:nowrap;">
                        <div style="font-size:12px;font-weight:600;color:var(--text-muted);">
                            {{ \Carbon\Carbon::parse($log->created_at)->format('M d, Y') }}
                        </div>
                        <div style="font-size:10px;color:var(--text-muted);">
                            {{ \Carbon\Carbon::parse($log->created_at)->format('h:i A') }} ·
                            {{ \Carbon\Carbon::parse($log->created_at)->diffForHumans() }}
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ $isAdmin ? 5 : 4 }}" style="text-align:center;color:var(--text-muted);padding:40px;">
                        <i data-lucide="file-text" style="width:32px;height:32px;display:block;margin:0 auto 10px;opacity:0.3;"></i>
                        {{ $isAdmin ? 'No activity logs found.' : 'You have no recorded activity yet.' }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:15px;">{{ $logs->links() }}</div>
</div>
@endsection
