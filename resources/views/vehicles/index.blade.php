@extends('layouts.app')

@section('title', 'Vehicle Management')
@section('breadcrumbs', 'Vehicles')
@section('page_title', 'Vehicle Management')

@section('content')
<div class="card table-card">
    <div class="chart-header" style="display: flex; flex-direction: column; align-items: flex-start; gap: 16px; width: 100%; border-bottom: none; padding-bottom: 0;">
        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; flex-wrap: wrap; gap: 15px;">
            <div class="chart-title-block">
                <span class="chart-title">Fleet Vehicles</span>
                <span class="chart-subtitle">Manage fleet vehicles, status, and driver assignments</span>
            </div>
            @can('vehicles.create')
            <a href="{{ route('vehicles.create') }}" class="btn-signin" style="padding: 10px 18px; width: auto; display: inline-flex; align-items: center; gap: 8px; font-size: 12px; margin: 0; white-space: nowrap;">
                <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                <span>Register Vehicle</span>
            </a>
            @endcan
        </div>
        <div style="width: 100%; display: flex; justify-content: flex-start; align-items: center; background: rgba(0,0,0,0.02); padding: 12px 16px; border-radius: 8px; border: 1px solid var(--border-color); box-shadow: inset 0 1px 2px rgba(0,0,0,0.02);">
            <form action="{{ route('vehicles.index') }}" method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap; margin: 0; width: 100%;">
                <input type="text" name="search" class="form-input" placeholder="Search vehicle #, registration..." value="{{ request('search') }}" style="height: 38px; padding: 5px 15px; font-size: 13px; max-width: 250px; margin: 0;">
                <select name="status" class="form-input" style="height: 38px; padding: 5px 15px; font-size: 13px; width: auto; margin: 0;">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <button type="submit" class="btn-signin" style="padding: 0 20px; height: 38px; margin: 0; width: auto; font-size: 12px; background: var(--primary); border-color: var(--primary);">Filter</button>
                @if(request('search') || request('status'))
                    <a href="{{ route('vehicles.index') }}" class="btn-signin" style="padding: 10px 20px; height: 38px; margin: 0; width: auto; font-size: 12px; background: #718096; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; color: #fff;">Clear</a>
                @endif
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="purity-table">
            <thead>
                <tr>
                    <th>Vehicle Number</th>
                    <th>Registration Name</th>
                    <th>Type / Category</th>
                    <th>Assigned Driver</th>
                    <th>Status</th>
                    @canany(['vehicles.edit', 'vehicles.delete'])
                    <th style="text-align: right;">Actions</th>
                    @endcanany
                </tr>
            </thead>
            <tbody>
                @forelse($vehicles as $vehicle)
                <tr>
                    <td>
                        <strong style="color: var(--text-color); font-size: 15px;">{{ $vehicle->vehicle_number }}</strong>
                    </td>
                    <td>
                        <span style="color: var(--text-muted); font-size: 14px;">{{ $vehicle->registration_name ?? 'N/A' }}</span>
                    </td>
                    <td>
                        <span style="color: var(--text-color); font-size: 14px;">{{ $vehicle->type ?? 'N/A' }}</span>
                    </td>
                    <td>
                        @if($vehicle->driver)
                            <div style="display: flex; flex-direction: column;">
                                <span style="font-weight: 700; color: var(--text-color);">{{ $vehicle->driver->name }}</span>
                                <span style="font-size: 11px; color: var(--text-muted);">{{ $vehicle->driver->phone_number }}</span>
                            </div>
                        @else
                            <span class="badge warning" style="font-size: 9px;">Unassigned</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $vehicle->status === 'active' ? 'active' : 'danger' }}" style="font-size: 10px;">
                            {{ $vehicle->status }}
                        </span>
                    </td>
                    @canany(['vehicles.edit', 'vehicles.delete'])
                    <td style="text-align: right;">
                        <div style="display: inline-flex; gap: 15px; align-items: center;">
                            @can('vehicles.edit')
                            <a href="{{ route('vehicles.edit', $vehicle->id) }}" style="color: var(--primary); text-decoration: none; font-weight: 700; font-size: 12px;">Edit</a>
                            @endcan
                            @can('vehicles.delete')
                            <form action="{{ route('vehicles.destroy', $vehicle->id) }}" method="POST" style="display: inline;">
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
                    <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 30px;">No vehicles found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div style="margin-top: 15px;">
        {{ $vehicles->links() }}
    </div>
</div>
@endsection
