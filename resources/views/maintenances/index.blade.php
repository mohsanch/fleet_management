@extends('layouts.app')

@section('title', 'Vehicle Maintenance Logs')
@section('breadcrumbs', 'Maintenance')
@section('page_title', 'Vehicle Maintenance Logs')

@section('content')
<div class="card table-card">
    <div class="chart-header" style="display: flex; flex-direction: column; align-items: flex-start; gap: 16px; width: 100%; border-bottom: none; padding-bottom: 0;">
        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; flex-wrap: wrap; gap: 15px;">
            <div class="chart-title-block">
                <span class="chart-title">Fleet Repairs & Services</span>
                <span class="chart-subtitle">View and record Hino Mobil Oil changes, filter replacements, tires, and general services</span>
            </div>
            @can('maintenance.create')
            <a href="{{ route('maintenances.create') }}" class="btn-signin" style="padding: 10px 18px; width: auto; display: inline-flex; align-items: center; gap: 8px; font-size: 12px; margin: 0; white-space: nowrap;">
                <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                <span>Log Maintenance</span>
            </a>
            @endcan
        </div>
        <div style="width: 100%; display: flex; justify-content: flex-start; align-items: center; background: rgba(0,0,0,0.02); padding: 12px 16px; border-radius: 8px; border: 1px solid var(--border-color); box-shadow: inset 0 1px 2px rgba(0,0,0,0.02);">
            <form action="{{ route('maintenances.index') }}" method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap; margin: 0; width: 100%;">
                <input type="text" name="search" class="form-input" placeholder="Search keyword..." value="{{ request('search') }}" style="height: 38px; padding: 5px 15px; font-size: 13px; max-width: 160px; margin: 0;">
                <select name="vehicle_id" class="form-input" style="height: 38px; padding: 5px 15px; font-size: 13px; width: auto; margin: 0;">
                    <option value="">All Vehicles</option>
                    @foreach($vehicles as $veh)
                        <option value="{{ $veh->id }}" {{ request('vehicle_id') == $veh->id ? 'selected' : '' }}>{{ $veh->vehicle_number }}</option>
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
                @if(request('search') || request('vehicle_id') || request('date_from') || request('date_to'))
                    <a href="{{ route('maintenances.index') }}" class="btn-signin" style="padding: 10px 20px; height: 38px; margin: 0; width: auto; font-size: 12px; background: #718096; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; color: #fff;">Clear</a>
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
                    <th>Service / Type</th>
                    <th>Vendor</th>
                    <th>Invoice Number</th>
                    <th>Remarks</th>
                    <th>Amount</th>
                    @canany(['maintenance.edit','maintenance.delete'])
                    <th style="text-align: right;">Actions</th>
                    @endcanany
                </tr>
            </thead>
            <tbody>
                @forelse($maintenances as $log)
                <tr>
                    <td>
                        <strong style="color: var(--text-color);">{{ \Carbon\Carbon::parse($log->maintenance_date)->format('M d, Y') }}</strong>
                    </td>
                    <td>
                        <strong style="color: var(--primary);">{{ $log->vehicle->vehicle_number }}</strong>
                    </td>
                    <td>
                        <span class="badge info" style="font-size: 10px; text-transform: uppercase;">{{ $log->maintenance_type }}</span>
                    </td>
                    <td>
                        <span style="font-weight: 700; color: var(--text-color);">{{ $log->vendor }}</span>
                    </td>
                    <td>
                        <span style="color: var(--text-muted); font-size: 13px;">{{ $log->invoice_number ?? 'N/A' }}</span>
                    </td>
                    <td>
                        <span style="color: var(--text-muted); font-size: 12px;">{{ Str::limit($log->remarks, 40) }}</span>
                    </td>
                    <td>
                        <strong style="color: #E53E3E; font-size: 15px;">Rs. {{ number_format($log->amount) }}</strong>
                    </td>
                    @canany(['maintenance.edit','maintenance.delete'])
                    <td style="text-align: right;">
                        <div style="display: inline-flex; gap: 15px; align-items: center;">
                            @can('maintenance.edit')
                                <a href="{{ route('maintenances.edit', $log->id) }}" style="color: var(--primary); text-decoration: none; font-weight: 700; font-size: 12px;">Edit</a>
                            @endcan
                            @can('maintenance.delete')
                                <form action="{{ route('maintenances.destroy', $log->id) }}" method="POST" style="display: inline;">
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
                    <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 30px;">No maintenance records found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div style="margin-top: 15px;">
        {{ $maintenances->links() }}
    </div>
</div>
@endsection
