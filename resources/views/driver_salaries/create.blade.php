@extends('layouts.app')

@section('title', 'Process Driver Salary')
@section('breadcrumbs', 'Payroll / Driver Salaries / Process')
@section('page_title', 'Process Driver Salary')

@section('content')
<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="chart-header" style="margin-bottom: 24px;">
        <div class="chart-title-block">
            <span class="chart-title">New Driver Salary</span>
            <span class="chart-subtitle">Enter gross pay, deductions, and pasgi adjustments</span>
        </div>
    </div>

    <form action="{{ route('driver-salaries.store') }}" method="POST" class="auth-form" style="max-width: 100%; gap: 20px;" id="salaryForm">
        @csrf

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="driver_id">Select Driver</label>
                <select name="driver_id" id="driver_id" class="form-input" style="height: 48px; padding: 10px 20px;" required>
                    <option value="" disabled selected>Select driver</option>
                    @foreach($drivers as $driver)
                        <option value="{{ $driver->id }}" data-salary="{{ $driver->base_salary }}" data-pasgi-outstanding="{{ $driver->remaining_pasgi }}" {{ old('driver_id') == $driver->id ? 'selected' : '' }}>
                            {{ $driver->name }}
                        </option>
                    @endforeach
                </select>
                @error('driver_id') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="salary_period">Salary Period (Month/Year)</label>
                <input type="month" name="salary_period" id="salary_period" class="form-input" value="{{ old('salary_period', date('Y-m')) }}" required>
                @error('salary_period') <span class="form-error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div style="border-top: 1px solid rgba(255,255,255,0.08); padding-top: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="gross_salary">Gross Salary (Rs.)</label>
                <input type="number" step="0.01" name="gross_salary" id="gross_salary" class="form-input" placeholder="Auto-filled from driver profile" value="{{ old('gross_salary') }}" required oninput="calcNet()">
                @error('gross_salary') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="fine">Fine / Deduction (Rs.)</label>
                <input type="number" step="0.01" name="fine" id="fine" class="form-input" placeholder="0.00" value="{{ old('fine', 0) }}" oninput="calcNet()">
                @error('fine') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="pasgi_adjustment">Pasgi Adjustment (Rs.)</label>
                <div style="display: flex; gap: 8px;">
                    <input type="number" step="0.01" name="pasgi_adjustment" id="pasgi_adjustment" class="form-input" placeholder="0.00" value="{{ old('pasgi_adjustment', 0) }}" oninput="calcNet()" style="flex: 1;">
                    <button type="button" id="btn-use-outstanding" class="btn-signin" style="margin: 0; width: auto; font-size: 11px; padding: 0 10px; height: 48px; background: #38A169; display: none;">Use Outstanding</button>
                </div>
                <span id="pasgi-outstanding-label" style="font-size: 11px; color: var(--text-muted); display: block; margin-top: 4px;"></span>
                @error('pasgi_adjustment') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="other_adjustment">Other Adjustment +/- (Rs.)</label>
                <input type="number" step="0.01" name="other_adjustment" id="other_adjustment" class="form-input" placeholder="0.00 (negative for deduction)" value="{{ old('other_adjustment', 0) }}" oninput="calcNet()">
                @error('other_adjustment') <span class="form-error">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Live Net Payable Preview -->
        <div style="background: rgba(99, 102, 241, 0.08); border: 1px solid rgba(99, 102, 241, 0.2); border-radius: 10px; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 13px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Net Payable (Preview)</span>
            <strong id="netPreview" style="font-size: 22px; color: var(--primary);">Rs. 0</strong>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 20px;">
            <div class="form-group">
                <label for="payment_date">Payment Date</label>
                <input type="date" name="payment_date" id="payment_date" class="form-input" value="{{ old('payment_date', date('Y-m-d')) }}" required>
                @error('payment_date') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="payment_status">Payment Status</label>
                <select name="payment_status" id="payment_status" class="form-input" style="height: 48px; padding: 10px 20px;" required>
                    <option value="paid" {{ old('payment_status', 'paid') === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="pending" {{ old('payment_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
                @error('payment_status') <span class="form-error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group">
            <label for="remarks">Remarks</label>
            <textarea name="remarks" id="remarks" class="form-input" placeholder="Any salary notes or adjustments reason..." style="height: 75px; padding: 10px 20px; resize: none;">{{ old('remarks') }}</textarea>
        </div>

        <div style="margin-top: 10px; display: flex; gap: 15px;">
            <button type="submit" class="btn-signin" style="margin: 0; width: auto;">Process Salary</button>
            <a href="{{ route('driver-salaries.index') }}" class="btn-signin" style="margin: 0; width: auto; background: #718096; text-decoration: none; color: white; display: inline-flex; align-items: center; justify-content: center; padding: 0 20px;">Cancel</a>
        </div>
    </form>
</div>

<script>
    // Auto-fill gross salary and Pasgi outstanding suggestions
    document.getElementById('driver_id').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const baseSalary = selected.getAttribute('data-salary') || '';
        const outstanding = parseFloat(selected.getAttribute('data-pasgi-outstanding')) || 0;
        
        if (baseSalary) {
            document.getElementById('gross_salary').value = baseSalary;
        }

        const label = document.getElementById('pasgi-outstanding-label');
        const btn = document.getElementById('btn-use-outstanding');
        
        if (outstanding > 0) {
            label.innerHTML = `Outstanding Pasgi: <strong>Rs. ${outstanding.toLocaleString()}</strong>`;
            btn.style.display = 'block';
            btn.onclick = function() {
                document.getElementById('pasgi_adjustment').value = outstanding;
                calcNet();
            };
        } else {
            label.innerHTML = 'No outstanding Pasgi balance';
            btn.style.display = 'none';
        }
        
        calcNet();
    });

    function calcNet() {
        const gross = parseFloat(document.getElementById('gross_salary').value) || 0;
        const fine  = parseFloat(document.getElementById('fine').value) || 0;
        const pasgi = parseFloat(document.getElementById('pasgi_adjustment').value) || 0;
        const other = parseFloat(document.getElementById('other_adjustment').value) || 0;
        const net   = gross - fine - pasgi + other;
        document.getElementById('netPreview').textContent = 'Rs. ' + net.toLocaleString('en-PK', { minimumFractionDigits: 0 });
        document.getElementById('netPreview').style.color = net >= 0 ? 'var(--primary)' : '#E53E3E';
    }

    // Trigger once on load if a driver is already selected
    window.addEventListener('load', function() {
        if (document.getElementById('driver_id').value) {
            document.getElementById('driver_id').dispatchEvent(new Event('change'));
        } else {
            calcNet();
        }
    });
</script>
@endsection
