@extends('layouts.app')

@section('title', 'Activity Logs')
@section('breadcrumbs', 'Admin / Activity Logs')
@section('page_title', 'System Activity Logs')

@section('content')

<div class="card table-card">
    <div class="chart-header" style="margin-bottom:20px;align-items:center;flex-wrap:wrap;gap:14px;">
        <div class="chart-title-block">
            <span class="chart-title">System Activity Log</span>
            <span class="chart-subtitle">Full audit trail of all actions performed across the system</span>
        </div>
        <a href="{{ route('admin.dashboard') }}" style="font-size:12px;color:var(--primary);font-weight:700;text-decoration:none;white-space:nowrap;">
            ← Back to Dashboard
        </a>
    </div>

    {{-- Filters --}}
    <form action="{{ route('admin.logs.index') }}" method="GET" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px;padding:14px 16px;background:var(--bg-light);border:1px solid var(--border-color);border-radius:10px;align-items:center;">
        <input type="text" name="search" class="form-input" placeholder="Search description, user, or model…" value="{{ request('search') }}" style="height:36px;padding:5px 14px;font-size:13px;max-width:240px;margin:0;flex:1;min-width:160px;">

        <select name="action" class="form-input" style="height:36px;padding:5px 14px;font-size:13px;width:auto;margin:0;">
            <option value="">All Actions</option>
            @foreach(['CREATE','UPDATE','DELETE','LOGIN','UPDATE_SETTINGS'] as $act)
            <option value="{{ $act }}" {{ request('action') === $act ? 'selected' : '' }}>{{ $act }}</option>
            @endforeach
        </select>

        <input type="date" name="date_from" class="form-input" value="{{ request('date_from') }}" style="height:36px;padding:5px 14px;font-size:13px;width:auto;margin:0;" title="From date">
        <input type="date" name="date_to" class="form-input" value="{{ request('date_to') }}" style="height:36px;padding:5px 14px;font-size:13px;width:auto;margin:0;" title="To date">

        <button type="submit" class="btn-signin" style="height:36px;width:auto;padding:0 16px;font-size:12px;margin:0;">
            <i data-lucide="filter" style="width:12px;height:12px;display:inline;vertical-align:middle;margin-right:4px;"></i>Filter
        </button>
        @if(request('search') || request('action') || request('date_from') || request('date_to'))
        <a href="{{ route('admin.logs.index') }}" class="btn-signin" style="height:36px;padding:0 14px;font-size:12px;background:#718096;display:inline-flex;align-items:center;text-decoration:none;color:#fff;margin:0;">Clear</a>
        @endif
    </form>

    {{-- Table --}}
    <div class="table-responsive">
        <table class="purity-table">
            <thead>
                <tr>
                    <th style="width:120px;">Action</th>
                    <th style="width:160px;">User</th>
                    <th>Description</th>
                    <th style="width:120px;">Model</th>
                    <th style="width:140px;">Date & Time</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td>
                        @php
                            $action = strtoupper($log->action ?? '');
                            $cfg = match(true) {
                                str_contains($action,'CREATE')  => ['bg'=>'rgba(56,161,105,0.12)','color'=>'#38A169'],
                                str_contains($action,'UPDATE')  => ['bg'=>'rgba(66,153,225,0.12)','color'=>'#4299E1'],
                                str_contains($action,'DELETE')  => ['bg'=>'rgba(229,62,62,0.12)','color'=>'#E53E3E'],
                                str_contains($action,'LOGIN')   => ['bg'=>'rgba(99,102,241,0.12)','color'=>'var(--primary)'],
                                default                         => ['bg'=>'rgba(113,128,150,0.12)','color'=>'#718096'],
                            };
                        @endphp
                        <span style="font-size:10px;font-weight:700;padding:3px 9px;border-radius:20px;background:{{ $cfg['bg'] }};color:{{ $cfg['color'] }};text-transform:uppercase;letter-spacing:0.5px;white-space:nowrap;">
                            {{ $action }}
                        </span>
                    </td>
                    <td>
                        @if($log->user)
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="width:28px;height:28px;border-radius:50%;background:var(--primary);color:#fff;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                {{ strtoupper(substr($log->user->name, 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-size:12px;font-weight:600;color:var(--text-color);">{{ $log->user->name }}</div>
                                <div style="font-size:10px;color:var(--text-muted);">{{ $log->user->email }}</div>
                            </div>
                        </div>
                        @else
                        <span style="font-size:11px;color:var(--text-muted);">System</span>
                        @endif
                    </td>
                    <td style="font-size:12px;color:var(--text-muted);max-width:350px;">
                        {{ $log->reason ?? $log->description ?? '—' }}
                    </td>
                    <td style="font-size:11px;color:var(--text-muted);">
                        @if($log->model_type)
                        <span style="background:var(--bg-light);border:1px solid var(--border-color);border-radius:6px;padding:2px 8px;font-size:10px;">
                            {{ class_basename($log->model_type) }}
                            @if($log->model_id) #{{ $log->model_id }} @endif
                        </span>
                        @else
                        —
                        @endif
                    </td>
                    <td style="font-size:11px;color:var(--text-muted);white-space:nowrap;">
                        @if($log->created_at)
                        <div>{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y') }}</div>
                        <div style="font-size:10px;">{{ \Carbon\Carbon::parse($log->created_at)->format('h:i A') }}</div>
                        @else
                        —
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center;color:var(--text-muted);padding:50px;">
                        <i data-lucide="file-text" style="width:36px;height:36px;margin-bottom:10px;opacity:0.3;display:block;margin-left:auto;margin-right:auto;"></i>
                        No activity logs found matching your filters.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:15px;">{{ $logs->links() }}</div>
</div>

@endsection
