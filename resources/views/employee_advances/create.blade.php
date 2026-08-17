@extends('layouts.app')

@section('title', 'Issue Employee Advance')
@section('breadcrumbs', 'Payroll / Employee Advances / Issue')
@section('page_title', 'Issue Employee Advance')

@section('content')
<div class="card" style="max-width: 480px; margin: 0 auto;">
    <div class="chart-header" style="margin-bottom: 24px;">
        <div class="chart-title-block">
            <span class="chart-title">New Employee Advance</span>
            <span class="chart-subtitle">Issue a cash advance to a staff member — deducted at salary time</span>
        </div>
    </div>

    <form action="{{ route('employee-advances.store') }}" method="POST" class="auth-form" style="max-width: 100%; gap: 20px;">
        @csrf

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="date">Date</label>
                <input type="date" name="date" id="date" class="form-input" value="{{ old('date', date('Y-m-d')) }}" required>
                @error('date') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="amount">Amount (Rs.)</label>
                <input type="number" step="0.01" name="amount" id="amount" class="form-input" placeholder="e.g. 3000" value="{{ old('amount') }}" required>
                @error('amount') <span class="form-error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group">
            <label for="employee_id">Select Employee</label>
            <select name="employee_id" id="employee_id" class="form-input" style="height: 48px; padding: 10px 20px;" required>
                <option value="" disabled selected>Select employee</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                        {{ $employee->name }} ({{ $employee->designation }})
                    </option>
                @endforeach
            </select>
            @error('employee_id') <span class="form-error">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label for="remarks">Remarks / Reason</label>
            <textarea name="remarks" id="remarks" class="form-input" placeholder="Reason for advance, e.g. Medical emergency, school fees..." style="height: 80px; padding: 10px 20px; resize: none;">{{ old('remarks') }}</textarea>
            @error('remarks') <span class="form-error">{{ $message }}</span> @enderror
        </div>

        <div style="margin-top: 10px; display: flex; gap: 15px;">
            <button type="submit" class="btn-signin" style="margin: 0; width: auto;">Issue Advance</button>
            <a href="{{ route('employee-advances.index') }}" class="btn-signin" style="margin: 0; width: auto; background: #718096; text-decoration: none; color: white; display: inline-flex; align-items: center; justify-content: center; padding: 0 20px;">Cancel</a>
        </div>
    </form>
</div>
@endsection
