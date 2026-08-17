@extends('layouts.app')

@section('title', 'Daily Operations Log')
@section('breadcrumbs', 'Daily Data')
@section('page_title', 'Daily Operations Log')

@section('content')
<div class="card table-card">
    <div class="chart-header" style="display: flex; flex-direction: column; align-items: flex-start; gap: 16px; width: 100%; border-bottom: none; padding-bottom: 0;">
        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; flex-wrap: wrap; gap: 15px;">
            <div class="chart-title-block">
                <span class="chart-title">Daily operational activity logs</span>
                <span class="chart-subtitle">View and record driver runs, diesel metrics, and advancements</span>
            </div>
            @can('daily-data.create')
            <a href="{{ route('daily-data.create') }}" class="btn-signin" style="padding: 10px 18px; width: auto; display: inline-flex; align-items: center; gap: 8px; font-size: 12px; margin: 0; white-space: nowrap;">
                <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                <span>Log Daily Activity</span>
            </a>
            @endcan
        </div>
        <div style="width: 100%; display: flex; justify-content: flex-start; align-items: center; background: rgba(0,0,0,0.02); padding: 12px 16px; border-radius: 8px; border: 1px solid var(--border-color); box-shadow: inset 0 1px 2px rgba(0,0,0,0.02);">
            <form action="{{ route('daily-data.index') }}" method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap; margin: 0; width: 100%;">
                <input type="text" name="search" class="form-input" placeholder="Search keyword..." value="{{ request('search') }}" style="height: 38px; padding: 5px 15px; font-size: 13px; max-width: 150px; margin: 0;">
                <select name="vehicle_id" class="form-input" style="height: 38px; padding: 5px 15px; font-size: 13px; width: auto; margin: 0;">
                    <option value="">All Vehicles</option>
                    @foreach($vehicles as $veh)
                        <option value="{{ $veh->id }}" {{ request('vehicle_id') == $veh->id ? 'selected' : '' }}>{{ $veh->vehicle_number }}</option>
                    @endforeach
                </select>
                <select name="driver_id" class="form-input" style="height: 38px; padding: 5px 15px; font-size: 13px; width: auto; margin: 0;">
                    <option value="">All Drivers</option>
                    @foreach($drivers as $drv)
                        <option value="{{ $drv->id }}" {{ request('driver_id') == $drv->id ? 'selected' : '' }}>{{ $drv->name }}</option>
                    @endforeach
                </select>
                <div style="display: flex; align-items: center; gap: 5px;">
                    <span style="font-size: 11px; color: var(--text-muted); white-space: nowrap;">From:</span>
                    <input type="date" name="date_from" class="form-input" value="{{ request('date_from') }}" style="height: 38px; padding: 5px 10px; font-size: 13px; width: auto; margin: 0;">
                </div>
                <div style="display: flex; align-items: center; gap: 5px;">
                    <span style="font-size: 11px; color: var(--text-muted); white-space: nowrap;">To:</span>
                    <input type="date" name="date_to" class="form-input" value="{{ request('date_to') }}" style="height: 38px; padding: 5px 10px; font-size: 13px; width: auto; margin: 0;">
                </div>
                <button type="submit" class="btn-signin" style="padding: 0 20px; height: 38px; margin: 0; width: auto; font-size: 12px; background: var(--primary); border-color: var(--primary);">Filter</button>
                @if(request('search') || request('vehicle_id') || request('driver_id') || request('date_from') || request('date_to'))
                    <a href="{{ route('daily-data.index') }}" class="btn-signin" style="padding: 10px 20px; height: 38px; margin: 0; width: auto; font-size: 12px; background: #718096; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; color: #fff;">Clear</a>
                @endif
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="purity-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Vehicle</th>
                    <th>Driver Assigned</th>
                    <th>Diesel (Amount / Liters)</th>
                    <th>Kilometers (Main / Local / Total)</th>
                    <th>Driver Pasgi Advance</th>
                    @canany(['daily-data.edit','daily-data.delete'])
                    <th style="text-align: right;">Actions</th>
                    @endcanany
                </tr>
            </thead>
            <tbody>
                @forelse($dailyData as $log)
                <tr>
                    <td>
                        <strong style="color: var(--text-color);">{{ \Carbon\Carbon::parse($log->date)->format('M d, Y') }}</strong>
                    </td>
                    <td>
                        <strong style="color: var(--primary);">{{ $log->vehicle->vehicle_number }}</strong>
                    </td>
                    <td>
                        <span style="font-weight: 700; color: var(--text-color);">{{ $log->driver->name }}</span>
                    </td>
                    <td>
                        <div style="display: flex; flex-direction: column;">
                            <span style="font-weight: 700; color: var(--text-color);">Rs. {{ number_format($log->daily_diesel_amount) }}</span>
                            <span style="font-size: 11px; color: var(--text-muted);">{{ $log->daily_diesel_liters }} Liters</span>
                        </div>
                    </td>
                    <td>
                        <div style="display: flex; flex-direction: column;">
                            <span style="font-weight: 700; color: var(--text-color);">{{ $log->total_km }} KM</span>
                            <span style="font-size: 11px; color: var(--text-muted);">Main: {{ $log->main_km }} | Local: {{ $log->local_km }}</span>
                        </div>
                    </td>
                    <td>
                        <strong style="color: #E53E3E;">Rs. {{ number_format($log->pasgi_given) }}</strong>
                    </td>
                    @canany(['daily-data.edit','daily-data.delete'])
                    <td style="text-align: right;">
                        <div style="display: inline-flex; gap: 15px; align-items: center;">
                            @can('daily-data.edit')
                                @php
                                    $cutoffDays = (int) \App\Models\Setting::where('key', 'lock_cutoff_days')->value('value') ?: 3;
                                    $isLocked = \Carbon\Carbon::parse($log->date)->diffInDays(now()) > $cutoffDays && !auth()->user()->hasRole('Super Admin');
                                @endphp
                                @if($isLocked)
                                    <span style="color: var(--text-muted); font-size: 11px; font-style: italic;">
                                        <i data-lucide="lock" style="width: 12px; height: 12px; display: inline; vertical-align: middle;"></i> Locked
                                    </span>
                                @else
                                    <a href="{{ route('daily-data.edit', $log->id) }}" style="color: var(--primary); text-decoration: none; font-weight: 700; font-size: 12px;">Edit</a>
                                @endif
                            @endcan
                            @can('daily-data.delete')
                                <form action="{{ route('daily-data.destroy', $log->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="delete-btn" style="background: none; border: none; color: #E53E3E; cursor: pointer; font-weight: 700; font-size: 12px; padding: 0;">Delete</button>
                                </form>
                            @endcan
                        </div>
                    </td>
                    @endcanany
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 30px;">No operational logs found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div style="margin-top: 15px;">
        {{ $dailyData->links() }}
    </div>
</div>
@endsection
