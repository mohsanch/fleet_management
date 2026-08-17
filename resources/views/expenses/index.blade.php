@extends('layouts.app')

@section('title', 'Fleet Expenses')
@section('breadcrumbs', 'Financials / Expenses')
@section('page_title', 'Fleet Expenses')

@section('content')
<div class="card table-card">
    <div class="chart-header" style="display: flex; flex-direction: column; align-items: flex-start; gap: 16px; width: 100%; border-bottom: none; padding-bottom: 0;">
        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; flex-wrap: wrap; gap: 15px;">
            <div class="chart-title-block">
                <span class="chart-title">General Fleet Expenses</span>
                <span class="chart-subtitle">View and log vehicle costs, yard costs, parts purchased, and business overheads</span>
            </div>
            @can('finance.create')
            <a href="{{ route('expenses.create') }}" class="btn-signin" style="padding: 10px 18px; width: auto; display: inline-flex; align-items: center; gap: 8px; font-size: 12px; margin: 0; white-space: nowrap;">
                <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                <span>Log Expense</span>
            </a>
            @endcan
        </div>
        <div style="width: 100%; display: flex; justify-content: flex-start; align-items: center; background: rgba(0,0,0,0.02); padding: 12px 16px; border-radius: 8px; border: 1px solid var(--border-color); box-shadow: inset 0 1px 2px rgba(0,0,0,0.02);">
            <form action="{{ route('expenses.index') }}" method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap; margin: 0; width: 100%;">
                <input type="text" name="search" class="form-input" placeholder="Search keyword..." value="{{ request('search') }}" style="height: 38px; padding: 5px 15px; font-size: 13px; max-width: 160px; margin: 0;">
                <select name="category_id" class="form-input" style="height: 38px; padding: 5px 15px; font-size: 13px; width: auto; margin: 0;">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
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
                @if(request('search') || request('category_id') || request('vehicle_id') || request('date_from') || request('date_to'))
                    <a href="{{ route('expenses.index') }}" class="btn-signin" style="padding: 10px 20px; height: 38px; margin: 0; width: auto; font-size: 12px; background: #718096; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; color: #fff;">Clear</a>
                @endif
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="purity-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Category</th>
                    <th>Vehicle Assigned</th>
                    <th>Spender / Employee</th>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Created By</th>
                    @canany(['finance.edit','finance.delete'])
                    <th style="text-align: right;">Actions</th>
                    @endcanany
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $expense)
                <tr>
                    <td>
                        <strong style="color: var(--text-color);">{{ \Carbon\Carbon::parse($expense->date)->format('M d, Y') }}</strong>
                    </td>
                    <td>
                        <span class="badge danger" style="font-size: 10px; color: #E53E3E; background: rgba(229, 62, 62, 0.1);">{{ $expense->category->name }}</span>
                    </td>
                    <td>
                        @if($expense->vehicle)
                            <strong style="color: var(--primary);">{{ $expense->vehicle->vehicle_number }}</strong>
                        @else
                            <span class="badge warning" style="font-size: 9px;">General</span>
                        @endif
                    </td>
                    <td>
                        @if($expense->employee)
                            <span style="font-weight: 700; color: var(--text-color);">{{ $expense->employee->name }}</span>
                        @elseif($expense->employee_name)
                            <span style="color: var(--text-color);">{{ $expense->employee_name }}</span>
                        @else
                            <span style="color: var(--text-muted); font-size: 12px; font-style: italic;">N/A</span>
                        @endif
                    </td>
                    <td>
                        <span style="color: var(--text-muted); font-size: 13px;">{{ Str::limit($expense->description, 50) }}</span>
                    </td>
                    <td>
                        <strong style="color: #E53E3E; font-size: 15px;">Rs. {{ number_format($expense->amount) }}</strong>
                    </td>
                    <td>
                        <span style="font-size: 12px; color: var(--text-muted);">{{ $expense->creator->name }}</span>
                    </td>
                    @canany(['finance.edit','finance.delete'])
                    <td style="text-align: right;">
                        <div style="display: inline-flex; gap: 15px; align-items: center;">
                            @can('finance.edit')
                                <a href="{{ route('expenses.edit', $expense->id) }}" style="color: var(--primary); text-decoration: none; font-weight: 700; font-size: 12px;">Edit</a>
                            @endcan
                            @can('finance.delete')
                                <form action="{{ route('expenses.destroy', $expense->id) }}" method="POST" style="display: inline;">
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
                    <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 30px;">No expense records found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div style="margin-top: 15px;">
        {{ $expenses->links() }}
    </div>
</div>
@endsection
