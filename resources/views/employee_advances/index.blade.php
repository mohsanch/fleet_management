@extends('layouts.app')

@section('title', 'Employee Advances')
@section('breadcrumbs', 'Payroll / Employee Advances')
@section('page_title', 'Employee Advances')

@section('content')
<div class="card table-card">
    <div class="chart-header" style="display: flex; flex-direction: column; align-items: flex-start; gap: 16px; width: 100%; border-bottom: none; padding-bottom: 0;">
        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; flex-wrap: wrap; gap: 15px;">
            <div class="chart-title-block">
                <span class="chart-title">Employee Advance Ledger</span>
                <span class="chart-subtitle">Track cash advances issued to staff — deducted during salary processing</span>
            </div>
            @can('advances.create')
            <a href="{{ route('employee-advances.create') }}" class="btn-signin" style="padding: 10px 18px; width: auto; display: inline-flex; align-items: center; gap: 8px; font-size: 12px; margin: 0; white-space: nowrap;">
                <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                <span>Issue Advance</span>
            </a>
            @endcan
        </div>
        <div style="width: 100%; display: flex; justify-content: flex-start; align-items: center; background: rgba(0,0,0,0.02); padding: 12px 16px; border-radius: 8px; border: 1px solid var(--border-color); box-shadow: inset 0 1px 2px rgba(0,0,0,0.02);">
            <form action="{{ route('employee-advances.index') }}" method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap; margin: 0; width: 100%;">
                <input type="text" name="search" class="form-input" placeholder="Search employee, remarks..." value="{{ request('search') }}" style="height: 38px; padding: 5px 15px; font-size: 13px; max-width: 240px; margin: 0;">
                <input type="date" name="date" class="form-input" value="{{ request('date') }}" style="height: 38px; padding: 5px 15px; font-size: 13px; width: auto; margin: 0;">
                <button type="submit" class="btn-signin" style="padding: 0 20px; height: 38px; margin: 0; width: auto; font-size: 12px; background: var(--primary); border-color: var(--primary);">Filter</button>
                @if(request('search') || request('date'))
                    <a href="{{ route('employee-advances.index') }}" class="btn-signin" style="padding: 10px 20px; height: 38px; margin: 0; width: auto; font-size: 12px; background: #718096; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; color: #fff;">Clear</a>
                @endif
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="purity-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Employee</th>
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
                        <strong style="color: var(--text-color);">{{ $advance->employee->name }}</strong>
                        <br>
                        <small style="color: var(--text-muted); font-size: 11px;">{{ $advance->employee->designation }}</small>
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
                            @can('advances.edit')
                                <a href="{{ route('employee-advances.edit', $advance->id) }}" style="color: var(--primary); text-decoration: none; font-weight: 700; font-size: 12px;">Edit</a>
                            @endcan
                            @can('advances.delete')
                                <form action="{{ route('employee-advances.destroy', $advance->id) }}" method="POST" style="display: inline;">
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
                    <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 30px;">No advance records found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top: 15px;">{{ $advances->links() }}</div>
</div>
@endsection
