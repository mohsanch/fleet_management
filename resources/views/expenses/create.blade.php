@extends('layouts.app')

@section('title', 'Log Expense')
@section('breadcrumbs', 'Financials / Expenses / Log')
@section('page_title', 'Log Expense')

@section('content')
<div class="card" style="max-width: 550px; margin: 0 auto;">
    <div class="chart-header" style="margin-bottom: 24px;">
        <div class="chart-title-block">
            <span class="chart-title">New Expense Record</span>
            <span class="chart-subtitle">Record fleet costs, parts purchases, or general overheads</span>
        </div>
    </div>

    <form action="{{ route('expenses.store') }}" method="POST" class="auth-form" style="max-width: 100%; gap: 20px;">
        @csrf

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="date">Date</label>
                <input type="date" name="date" id="date" class="form-input" value="{{ old('date', date('Y-m-d')) }}" required>
                @error('date')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="amount">Expense Amount (Rs.)</label>
                <input type="number" step="0.01" name="amount" id="amount" class="form-input" placeholder="e.g. 8500" value="{{ old('amount') }}" required>
                @error('amount')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="form-group">
            <label for="category_id">Select Category</label>
            <select name="category_id" id="category_id" class="form-input" style="height: 48px; padding: 10px 20px;" required>
                <option value="" disabled selected>Select category</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }} data-name="{{ strtolower($category->name) }}">
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            @error('category_id')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <!-- Diesel Double-Count Warning -->
        <div id="diesel-warning" style="display:none; background: rgba(237,137,54,0.1); border: 1px solid #ED8936; border-radius: 10px; padding: 12px 16px; margin-top: -8px;">
            <div style="display: flex; align-items: flex-start; gap: 10px;">
                <i data-lucide="alert-triangle" style="width: 16px; height: 16px; color: #DD6B20; flex-shrink: 0; margin-top: 2px;"></i>
                <div>
                    <strong style="font-size: 13px; color: #DD6B20; display: block; margin-bottom: 3px;">Diesel Expense Warning</strong>
                    <span style="font-size: 12px; color: #744210;">Daily diesel amounts are already recorded in <strong>Fleet Daily Data</strong>. Logging diesel here as an expense may cause double-counting in reports. Only log here if this is a <em>separate</em> diesel purchase not covered in daily data.</span>
                </div>
            </div>
        </div>

        <script>
        document.getElementById('category_id').addEventListener('change', function() {
            const selectedName = this.options[this.selectedIndex].getAttribute('data-name') || '';
            const warning = document.getElementById('diesel-warning');
            warning.style.display = selectedName.includes('diesel') ? 'block' : 'none';
            if (warning.style.display === 'block') {
                lucide.createIcons();
            }
        });
        // Trigger on page load if old value selected
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('category_id').dispatchEvent(new Event('change'));
        });
        </script>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px;">
            <div class="form-group">
                <label for="vehicle_id">Associate with Vehicle (Optional)</label>
                <select name="vehicle_id" id="vehicle_id" class="form-input" style="height: 48px; padding: 10px 20px;">
                    <option value="">General (No Vehicle)</option>
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}" {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                            {{ $vehicle->vehicle_number }}
                        </option>
                    @endforeach
                </select>
                @error('vehicle_id')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="employee_id">Associate with Staff Employee (Optional)</label>
                <select name="employee_id" id="employee_id" class="form-input" style="height: 48px; padding: 10px 20px;">
                    <option value="">No Employee</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                            {{ $employee->name }} ({{ $employee->designation }})
                        </option>
                    @endforeach
                </select>
                @error('employee_id')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="form-group">
            <label for="employee_name">Recipient Name (If not in system staff list)</label>
            <input type="text" name="employee_name" id="employee_name" class="form-input" placeholder="e.g. Mechanic Name, Yard Shop Vendor" value="{{ old('employee_name') }}">
            @error('employee_name')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="description">Remarks / Description</label>
            <textarea name="description" id="description" class="form-input" placeholder="Explain what the expense was for..." style="height: 80px; padding: 10px 20px; resize: none;">{{ old('description') }}</textarea>
            @error('description')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div style="margin-top: 10px; display: flex; gap: 15px;">
            <button type="submit" class="btn-signin" style="margin: 0; width: auto;">Save Expense</button>
            <a href="{{ route('expenses.index') }}" class="btn-signin" style="margin: 0; width: auto; background: #718096; text-decoration: none; color: white; display: inline-flex; align-items: center; justify-content: center; padding: 0 20px;">Cancel</a>
        </div>
    </form>
</div>
@endsection
