@extends('layouts.app')

@section('title', 'Edit Driver Salary')
@section('breadcrumbs', 'Payroll / Driver Salaries / Edit')
@section('page_title', 'Edit Driver Salary')

@section('content')
<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="chart-header" style="margin-bottom: 24px;">
        <div class="chart-title-block">
            <span class="chart-title">Edit Driver Salary Record</span>
            <span class="chart-subtitle">Modify gross, deductions, or payment status for this salary</span>
        </div>
    </div>

    <form action="{{ route('driver-salaries.update', $driverSalary->id) }}" method="POST" class="auth-form" style="max-width: 100%; gap: 20px;">
        @csrf
        @method('PUT')

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="driver_id">Select Driver</label>
                <select name="driver_id" id="driver_id" class="form-input" style="height: 48px; padding: 10px 20px;" required>
                    @foreach($drivers as $driver)
                        <option value="{{ $driver->id }}" data-salary="{{ $driver->base_salary }}" {{ old('driver_id', $driverSalary->driver_id) == $driver->id ? 'selected' : '' }}>
                            {{ $driver->name }}
                        </option>
                    @endforeach
                </select>
                @error('driver_id') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="salary_period">Salary Period (Month/Year)</label>
                <input type="month" name="salary_period" id="salary_period" class="form-input" value="{{ old('salary_period', $driverSalary->salary_period) }}" required>
                @error('salary_period') <span class="form-error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div style="border-top: 1px solid rgba(255,255,255,0.08); padding-top: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="gross_salary">Gross Salary (Rs.)</label>
                <input type="number" step="0.01" name="gross_salary" id="gross_salary" class="form-input" value="{{ old('gross_salary', $driverSalary->gross_salary) }}" required oninput="calcNet()">
                @error('gross_salary') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="fine">Fine / Deduction (Rs.)</label>
                <input type="number" step="0.01" name="fine" id="fine" class="form-input" value="{{ old('fine', $driverSalary->fine ?? 0) }}" oninput="calcNet()">
                @error('fine') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="pasgi_adjustment">Pasgi Adjustment (Rs.)</label>
                <input type="number" step="0.01" name="pasgi_adjustment" id="pasgi_adjustment" class="form-input" value="{{ old('pasgi_adjustment', $driverSalary->pasgi_adjustment ?? 0) }}" oninput="calcNet()">
                @error('pasgi_adjustment') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="other_adjustment">Other Adjustment +/- (Rs.)</label>
                <input type="number" step="0.01" name="other_adjustment" id="other_adjustment" class="form-input" value="{{ old('other_adjustment', $driverSalary->other_adjustment ?? 0) }}" oninput="calcNet()">
                @error('other_adjustment') <span class="form-error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div style="background: rgba(99, 102, 241, 0.08); border: 1px solid rgba(99, 102, 241, 0.2); border-radius: 10px; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 13px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Net Payable (Preview)</span>
            <strong id="netPreview" style="font-size: 22px; color: var(--primary);">Rs. {{ number_format($driverSalary->net_payable) }}</strong>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 20px;">
            <div class="form-group">
                <label for="payment_date">Payment Date</label>
                <input type="date" name="payment_date" id="payment_date" class="form-input" value="{{ old('payment_date', $driverSalary->payment_date) }}" required>
                @error('payment_date') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="payment_status">Payment Status</label>
                <select name="payment_status" id="payment_status" class="form-input" style="height: 48px; padding: 10px 20px;" required>
                    <option value="paid" {{ old('payment_status', $driverSalary->payment_status) === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="pending" {{ old('payment_status', $driverSalary->payment_status) === 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
                @error('payment_status') <span class="form-error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group">
            <label for="remarks">Remarks</label>
            <textarea name="remarks" id="remarks" class="form-input" style="height: 75px; padding: 10px 20px; resize: none;">{{ old('remarks', $driverSalary->remarks) }}</textarea>
        </div>

        
        <div class="form-group" style="border-top: 1px solid var(--border-color); padding-top: 18px; margin-top: 8px;">
            <label for="edit_reason" style="color: var(--text-muted); font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                <i data-lucide="file-text" style="width: 12px; height: 12px; display: inline; margin-right: 4px;"></i>
                Reason for Edit (optional — saved to audit log)
            </label>
            <textarea name="edit_reason" id="edit_reason" class="form-input" style="height: 65px; padding: 10px 20px; resize: none; font-size: 13px;" placeholder="e.g. Corrected wrong amount, duplicate entry fixed...">{{ old('edit_reason') }}</textarea>
        </div>

        <div style="margin-top: 10px; display: flex; gap: 15px;">
            <button type="submit" class="btn-signin" style="margin: 0; width: auto;">Update Salary</button>
            <a href="{{ route('driver-salaries.index') }}" class="btn-signin" style="margin: 0; width: auto; background: #718096; text-decoration: none; color: white; display: inline-flex; align-items: center; justify-content: center; padding: 0 20px;">Cancel</a>
        </div>
    </form>
</div>

<script>
    function calcNet() {
        const gross = parseFloat(document.getElementById('gross_salary').value) || 0;
        const fine  = parseFloat(document.getElementById('fine').value) || 0;
        const pasgi = parseFloat(document.getElementById('pasgi_adjustment').value) || 0;
        const other = parseFloat(document.getElementById('other_adjustment').value) || 0;
        const net   = gross - fine - pasgi + other;
        document.getElementById('netPreview').textContent = 'Rs. ' + net.toLocaleString('en-PK', { minimumFractionDigits: 0 });
        document.getElementById('netPreview').style.color = net >= 0 ? 'var(--primary)' : '#E53E3E';
    }
    window.addEventListener('load', calcNet);
</script>
@endsection
