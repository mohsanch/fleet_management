@extends('layouts.app')

@section('title', 'Pasgi (Driver) Advances')
@section('breadcrumbs', 'Payroll / Pasgi Advances')
@section('page_title', 'Driver Pasgi Advances')

@section('content')
<div class="card table-card">
    <div class="chart-header" style="display: flex; flex-direction: column; align-items: flex-start; gap: 16px; width: 100%; border-bottom: none; padding-bottom: 0;">
        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; flex-wrap: wrap; gap: 15px;">
            <div class="chart-title-block">
                <span class="chart-title">Driver Pasgi Advance Ledger</span>
                <span class="chart-subtitle">Track cash advances issued to drivers — deducted during salary processing</span>
            </div>
            @can('advances.create')
            <a href="{{ route('pasgi-advances.create') }}" class="btn-signin" style="padding: 10px 18px; width: auto; display: inline-flex; align-items: center; gap: 8px; font-size: 12px; margin: 0; white-space: nowrap;">
                <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                <span>Issue Advance</span>
            </a>
            @endcan
        </div>
        <div style="width: 100%; display: flex; justify-content: flex-start; align-items: center; background: rgba(0,0,0,0.02); padding: 12px 16px; border-radius: 8px; border: 1px solid var(--border-color); box-shadow: inset 0 1px 2px rgba(0,0,0,0.02);">
            <form action="{{ route('pasgi-advances.index') }}" method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap; margin: 0; width: 100%;">
                <input type="text" name="search" class="form-input" placeholder="Search keyword..." value="{{ request('search') }}" style="height: 38px; padding: 5px 15px; font-size: 13px; max-width: 150px; margin: 0;">
                <select name="driver_id" class="form-input" style="height: 38px; padding: 5px 15px; font-size: 13px; width: auto; margin: 0;">
                    <option value="">All Drivers</option>
                    @foreach($drivers as $drv)
                        <option value="{{ $drv->id }}" {{ request('driver_id') == $drv->id ? 'selected' : '' }}>{{ $drv->name }}</option>
                    @endforeach
                </select>
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
                @if(request('search') || request('driver_id') || request('vehicle_id') || request('date_from') || request('date_to'))
                    <a href="{{ route('pasgi-advances.index') }}" class="btn-signin" style="padding: 10px 20px; height: 38px; margin: 0; width: auto; font-size: 12px; background: #718096; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; color: #fff;">Clear</a>
                @endif
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="purity-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Driver</th>
                    <th>Vehicle</th>
                    <th>Amount</th>
                    <th>Remarks</th>
                    <th>Issued By</th>
                    @canany(['advances.edit','advances.delete'])
                    <th style="text-align: right;">Actions</th>
                    @endcanany
                </tr>
            </thead>
            <tbody>
                @forelse($advances as $advance)
                <tr>
                    <td>
                        <strong style="color: var(--text-color);">{{ \Carbon\Carbon::parse($advance->date)->format('M d, Y') }}</strong>
                    </td>
                    <td>
                        <strong style="color: var(--text-color);">{{ $advance->driver->name }}</strong>
                    </td>
                    <td>
                        @if($advance->vehicle)
                            <span style="color: var(--primary); font-weight: 600;">{{ $advance->vehicle->vehicle_number }}</span>
                        @else
                            <span style="color: var(--text-muted); font-size: 12px; font-style: italic;">N/A</span>
                        @endif
                    </td>
                    <td>
                        <strong style="color: #E53E3E; font-size: 15px;">Rs. {{ number_format($advance->amount) }}</strong>
                    </td>
                    <td>
                        <span style="color: var(--text-muted); font-size: 13px;">{{ Str::limit($advance->remarks, 45) }}</span>
                    </td>
                    <td>
                        <span style="font-size: 12px; color: var(--text-muted);">{{ $advance->creator->name }}</span>
                    </td>
                    @canany(['advances.edit','advances.delete'])
                    <td style="text-align: right;">
                        <div style="display: inline-flex; gap: 15px; align-items: center;">
                            <a href="{{ route('pasgi-advances.driver-balance', $advance->driver_id) }}" style="color: #38A169; text-decoration: none; font-weight: 700; font-size: 12px;" title="View Running Balance & Record Recoveries">Balance</a>
                            @can('advances.edit')
                                <a href="{{ route('pasgi-advances.edit', $advance->id) }}" style="color: var(--primary); text-decoration: none; font-weight: 700; font-size: 12px;">Edit</a>
                            @endcan
                            @can('advances.delete')
                                <form action="{{ route('pasgi-advances.destroy', $advance->id) }}" method="POST" style="display: inline;">
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
                    <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 30px;">No advance records found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top: 15px;">{{ $advances->links() }}</div>
</div>
@endsection
