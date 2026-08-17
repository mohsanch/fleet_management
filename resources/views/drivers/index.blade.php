@extends('layouts.app')

@section('title', 'Driver Management')
@section('breadcrumbs', 'Drivers')
@section('page_title', 'Driver Management')

@section('content')

<div class="card table-card">
    <div class="chart-header" style="display: flex; flex-direction: column; align-items: flex-start; gap: 16px; width: 100%; border-bottom: none; padding-bottom: 0;">
        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; flex-wrap: wrap; gap: 15px;">
            <div class="chart-title-block">
                <span class="chart-title">Fleet Drivers</span>
                <span class="chart-subtitle">Add, edit, and view driver profile details and base salaries</span>
            </div>
            <a href="{{ route('drivers.create') }}" class="btn-signin" style="padding: 10px 18px; width: auto; display: inline-flex; align-items: center; gap: 8px; font-size: 12px; margin: 0; white-space: nowrap;">
                <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                <span>Add New Driver</span>
            </a>
        </div>
        <div style="width: 100%; display: flex; justify-content: flex-start; align-items: center; background: rgba(0,0,0,0.02); padding: 12px 16px; border-radius: 8px; border: 1px solid var(--border-color); box-shadow: inset 0 1px 2px rgba(0,0,0,0.02);">
            <form action="{{ route('drivers.index') }}" method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap; margin: 0; width: 100%;">
                <input type="text" name="search" class="form-input" placeholder="Search name, phone, license..." value="{{ request('search') }}" style="height: 38px; padding: 5px 15px; font-size: 13px; max-width: 250px; margin: 0;">
                
                <select name="status" class="form-input" style="height: 38px; padding: 5px 15px; font-size: 13px; width: auto; margin: 0;">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>

                <button type="submit" class="btn-signin" style="padding: 0 20px; height: 38px; margin: 0; width: auto; font-size: 12px; background: var(--primary); border-color: var(--primary);">Filter</button>
                @if(request('search') || request('status'))
                    <a href="{{ route('drivers.index') }}" class="btn-signin" style="padding: 10px 20px; height: 38px; margin: 0; width: auto; font-size: 12px; background: #718096; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; color: #fff;">Clear</a>
                @endif
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="purity-table">
            <thead>
                <tr>
                    <th>Driver Details</th>
                    <th>License Number</th>
                    <th>Base Salary</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($drivers as $driver)
                <tr>
                    <td>
                        <div class="item-profile">
                            <div class="avatar-icon" style="background: rgba(49, 151, 149, 0.1); color: var(--primary);">
                                DR
                            </div>
                            <div class="item-details">
                                <span class="item-title">{{ $driver->name }}</span>
                                <span class="item-subtitle">Phone: {{ $driver->phone }}</span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span style="font-weight: 600; color: var(--text-color); font-size: 13px;">
                            {{ $driver->license_number }}
                        </span>
                    </td>
                    <td>
                        <strong>Rs. {{ number_format($driver->base_salary) }}</strong>
                    </td>
                    <td>
                        <span class="badge {{ $driver->status === 'active' ? 'active' : ($driver->status === 'suspended' ? 'danger' : 'warning') }}" style="font-size: 10px;">
                            {{ $driver->status }}
                        </span>
                    </td>
                    <td style="text-align: right;">
                        <div style="display: inline-flex; gap: 15px; align-items: center;">
                            <a href="{{ route('drivers.edit', $driver->id) }}" style="color: var(--primary); text-decoration: none; font-weight: 700; font-size: 12px;">Edit</a>
                            
                            <form action="{{ route('drivers.destroy', $driver->id) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="delete-btn" style="background: none; border: none; color: #E53E3E; cursor: pointer; font-weight: 700; font-size: 12px; padding: 0;">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 30px;">No drivers found in the system.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div style="margin-top: 15px;">
        {{ $drivers->links() }}
    </div>
</div>
@endsection
