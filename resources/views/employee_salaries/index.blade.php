@extends('layouts.app')

@section('title', 'Employee Salaries')
@section('breadcrumbs', 'Payroll / Employee Salaries')
@section('page_title', 'Employee Salary Payroll')

@section('content')
<div class="card table-card">
    <div class="chart-header" style="display: flex; flex-direction: column; align-items: flex-start; gap: 16px; width: 100%; border-bottom: none; padding-bottom: 0;">
        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; flex-wrap: wrap; gap: 15px;">
            <div class="chart-title-block">
                <span class="chart-title">Employee Salary Disbursements</span>
                <span class="chart-subtitle">View gross pay, deductions, advance adjustments and net payable per employee</span>
            </div>
            @can('payroll.create')
            <a href="{{ route('employee-salaries.create') }}" class="btn-signin" style="padding: 10px 18px; width: auto; display: inline-flex; align-items: center; gap: 8px; font-size: 12px; margin: 0; white-space: nowrap;">
                <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                <span>Process Salary</span>
            </a>
            @endcan
        </div>
        <div style="width: 100%; display: flex; justify-content: flex-start; align-items: center; background: rgba(0,0,0,0.02); padding: 12px 16px; border-radius: 8px; border: 1px solid var(--border-color); box-shadow: inset 0 1px 2px rgba(0,0,0,0.02);">
            <form action="{{ route('employee-salaries.index') }}" method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap; margin: 0; width: 100%;">
                <input type="text" name="search" class="form-input" placeholder="Search employee, status..." value="{{ request('search') }}" style="height: 38px; padding: 5px 15px; font-size: 13px; max-width: 240px; margin: 0;">
                <input type="month" name="period" class="form-input" value="{{ request('period') }}" style="height: 38px; padding: 5px 15px; font-size: 13px; width: auto; margin: 0;">
                <button type="submit" class="btn-signin" style="padding: 0 20px; height: 38px; margin: 0; width: auto; font-size: 12px; background: var(--primary); border-color: var(--primary);">Filter</button>
                @if(request('search') || request('period'))
                    <a href="{{ route('employee-salaries.index') }}" class="btn-signin" style="padding: 10px 20px; height: 38px; margin: 0; width: auto; font-size: 12px; background: #718096; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; color: #fff;">Clear</a>
                @endif
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="purity-table">
            <thead>
                <tr>
                    <th>Period</th>
                    <th>Employee</th>
                    <th>Gross Salary</th>
                    <th>Fine</th>
                    <th>Advance Adj.</th>
                    <th>Other Adj.</th>
                    <th>Net Payable</th>
                    <th>Status</th>
                    @canany(['payroll.edit','payroll.delete'])
                    <th style="text-align: right;">Actions</th>
                    @endcanany
                </tr>
            </thead>
            <tbody>
                @forelse($salaries as $salary)
                <tr>
                    <td>
                        <strong style="color: var(--text-color);">{{ $salary->salary_period }}</strong>
                        <br>
                        <small style="color: var(--text-muted); font-size: 11px;">Paid: {{ \Carbon\Carbon::parse($salary->payment_date)->format('M d, Y') }}</small>
                    </td>
                    <td>
                        <strong style="color: var(--text-color);">{{ $salary->employee->name }}</strong>
                        <br>
                        <small style="color: var(--text-muted); font-size: 11px;">{{ $salary->employee->designation }}</small>
                    </td>
                    <td><span style="color: #38A169; font-weight: 600;">Rs. {{ number_format($salary->gross_salary) }}</span></td>
                    <td><span style="color: #E53E3E;">Rs. {{ number_format($salary->fine ?? 0) }}</span></td>
                    <td><span style="color: #E53E3E;">Rs. {{ number_format($salary->advance_adjustment ?? 0) }}</span></td>
                    <td>
                        <span style="color: {{ ($salary->other_adjustment ?? 0) >= 0 ? '#38A169' : '#E53E3E' }};">
                            Rs. {{ number_format($salary->other_adjustment ?? 0) }}
                        </span>
                    </td>
                    <td>
                        <strong style="font-size: 15px; color: var(--primary);">Rs. {{ number_format($salary->net_payable) }}</strong>
                    </td>
                    <td>
                        @if($salary->payment_status === 'paid')
                            <span class="badge success" style="font-size: 10px; background: rgba(56, 161, 105, 0.15); color: #38A169;">Paid</span>
                        @else
                            <span class="badge warning" style="font-size: 10px; background: rgba(236, 153, 75, 0.15); color: #EC994B;">Pending</span>
                        @endif
                    </td>
                    @canany(['payroll.edit','payroll.delete'])
                    <td style="text-align: right;">
                        <div style="display: inline-flex; gap: 15px; align-items: center;">
                            @can('payroll.edit')
                                <a href="{{ route('employee-salaries.edit', $salary->id) }}" style="color: var(--primary); text-decoration: none; font-weight: 700; font-size: 12px;">Edit</a>
                            @endcan
                            @can('payroll.delete')
                                <form action="{{ route('employee-salaries.destroy', $salary->id) }}" method="POST" style="display: inline;">
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
                    <td colspan="9" style="text-align: center; color: var(--text-muted); padding: 30px;">No salary records found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top: 15px;">{{ $salaries->links() }}</div>
</div>
@endsection
