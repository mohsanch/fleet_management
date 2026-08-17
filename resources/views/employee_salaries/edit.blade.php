@extends('layouts.app')

@section('title', 'Edit Employee Salary')
@section('breadcrumbs', 'Payroll / Employee Salaries / Edit')
@section('page_title', 'Edit Employee Salary')

@section('content')
<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="chart-header" style="margin-bottom: 24px;">
        <div class="chart-title-block">
            <span class="chart-title">Edit Employee Salary Record</span>
            <span class="chart-subtitle">Modify gross, deductions, or payment status for this salary</span>
        </div>
    </div>

    <form action="{{ route('employee-salaries.update', $employeeSalary->id) }}" method="POST" class="auth-form" style="max-width: 100%; gap: 20px;">
        @csrf
        @method('PUT')

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="employee_id">Select Employee</label>
                <select name="employee_id" id="employee_id" class="form-input" style="height: 48px; padding: 10px 20px;" required>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" data-salary="{{ $employee->base_salary }}" {{ old('employee_id', $employeeSalary->employee_id) == $employee->id ? 'selected' : '' }}>
                            {{ $employee->name }} ({{ $employee->designation }})
                        </option>
                    @endforeach
                </select>
                @error('employee_id') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="salary_period">Salary Period (Month/Year)</label>
                <input type="month" name="salary_period" id="salary_period" class="form-input" value="{{ old('salary_period', $employeeSalary->salary_period) }}" required>
                @error('salary_period') <span class="form-error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div style="border-top: 1px solid rgba(255,255,255,0.08); padding-top: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="gross_salary">Gross Salary (Rs.)</label>
                <input type="number" step="0.01" name="gross_salary" id="gross_salary" class="form-input" value="{{ old('gross_salary', $employeeSalary->gross_salary) }}" required oninput="calcNet()">
                @error('gross_salary') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="fine">Fine / Deduction (Rs.)</label>
                <input type="number" step="0.01" name="fine" id="fine" class="form-input" value="{{ old('fine', $employeeSalary->fine ?? 0) }}" oninput="calcNet()">
                @error('fine') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="advance_adjustment">Advance Adjustment (Rs.)</label>
                <input type="number" step="0.01" name="advance_adjustment" id="advance_adjustment" class="form-input" value="{{ old('advance_adjustment', $employeeSalary->advance_adjustment ?? 0) }}" oninput="calcNet()">
                @error('advance_adjustment') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="other_adjustment">Other Adjustment +/- (Rs.)</label>
                <input type="number" step="0.01" name="other_adjustment" id="other_adjustment" class="form-input" value="{{ old('other_adjustment', $employeeSalary->other_adjustment ?? 0) }}" oninput="calcNet()">
                @error('other_adjustment') <span class="form-error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div style="background: rgba(99, 102, 241, 0.08); border: 1px solid rgba(99, 102, 241, 0.2); border-radius: 10px; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 13px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Net Payable (Preview)</span>
            <strong id="netPreview" style="font-size: 22px; color: var(--primary);">Rs. {{ number_format($employeeSalary->net_payable) }}</strong>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 20px;">
            <div class="form-group">
                <label for="payment_date">Payment Date</label>
                <input type="date" name="payment_date" id="payment_date" class="form-input" value="{{ old('payment_date', $employeeSalary->payment_date) }}" required>
                @error('payment_date') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="payment_status">Payment Status</label>
                <select name="payment_status" id="payment_status" class="form-input" style="height: 48px; padding: 10px 20px;" required>
                    <option value="paid" {{ old('payment_status', $employeeSalary->payment_status) === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="pending" {{ old('payment_status', $employeeSalary->payment_status) === 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
                @error('payment_status') <span class="form-error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group">
            <label for="remarks">Remarks</label>
            <textarea name="remarks" id="remarks" class="form-input" style="height: 75px; padding: 10px 20px; resize: none;">{{ old('remarks', $employeeSalary->remarks) }}</textarea>
        </div>

        <div style="margin-top: 10px; display: flex; gap: 15px;">
            <button type="submit" class="btn-signin" style="margin: 0; width: auto;">Update Salary</button>
            <a href="{{ route('employee-salaries.index') }}" class="btn-signin" style="margin: 0; width: auto; background: #718096; text-decoration: none; color: white; display: inline-flex; align-items: center; justify-content: center; padding: 0 20px;">Cancel</a>
        </div>
    </form>
</div>

<script>
    function calcNet() {
        const gross   = parseFloat(document.getElementById('gross_salary').value) || 0;
        const fine    = parseFloat(document.getElementById('fine').value) || 0;
        const advance = parseFloat(document.getElementById('advance_adjustment').value) || 0;
        const other   = parseFloat(document.getElementById('other_adjustment').value) || 0;
        const net     = gross - fine - advance + other;
        document.getElementById('netPreview').textContent = 'Rs. ' + net.toLocaleString('en-PK', { minimumFractionDigits: 0 });
        document.getElementById('netPreview').style.color = net >= 0 ? 'var(--primary)' : '#E53E3E';
    }
    window.addEventListener('load', calcNet);
</script>
@endsection
