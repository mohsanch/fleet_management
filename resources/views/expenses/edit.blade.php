@extends('layouts.app')

@section('title', 'Edit Expense')
@section('breadcrumbs', 'Financials / Expenses / Edit')
@section('page_title', 'Edit Expense')

@section('content')
<div class="card" style="max-width: 550px; margin: 0 auto;">
    <div class="chart-header" style="margin-bottom: 24px;">
        <div class="chart-title-block">
            <span class="chart-title">Edit Expense Record</span>
            <span class="chart-subtitle">Modify parameters for this expense transaction</span>
        </div>
    </div>

    <form action="{{ route('expenses.update', $expense->id) }}" method="POST" class="auth-form" style="max-width: 100%; gap: 20px;">
        @csrf
        @method('PUT')

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="date">Date</label>
                <input type="date" name="date" id="date" class="form-input" value="{{ old('date', $expense->date) }}" required>
                @error('date')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="amount">Expense Amount (Rs.)</label>
                <input type="number" step="0.01" name="amount" id="amount" class="form-input" value="{{ old('amount', $expense->amount) }}" required>
                @error('amount')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="form-group">
            <label for="category_id">Select Category</label>
            <select name="category_id" id="category_id" class="form-input" style="height: 48px; padding: 10px 20px;" required>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id', $expense->category_id) == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            @error('category_id')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px;">
            <div class="form-group">
                <label for="vehicle_id">Associate with Vehicle (Optional)</label>
                <select name="vehicle_id" id="vehicle_id" class="form-input" style="height: 48px; padding: 10px 20px;">
                    <option value="">General (No Vehicle)</option>
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}" {{ old('vehicle_id', $expense->vehicle_id) == $vehicle->id ? 'selected' : '' }}>
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
                        <option value="{{ $employee->id }}" {{ old('employee_id', $expense->employee_id) == $employee->id ? 'selected' : '' }}>
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
            <input type="text" name="employee_name" id="employee_name" class="form-input" value="{{ old('employee_name', $expense->employee_name) }}">
            @error('employee_name')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="description">Remarks / Description</label>
            <textarea name="description" id="description" class="form-input" style="height: 80px; padding: 10px 20px; resize: none;">{{ old('description', $expense->description) }}</textarea>
            @error('description')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        
        <div class="form-group" style="border-top: 1px solid var(--border-color); padding-top: 18px; margin-top: 8px;">
            <label for="edit_reason" style="color: var(--text-muted); font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                <i data-lucide="file-text" style="width: 12px; height: 12px; display: inline; margin-right: 4px;"></i>
                Reason for Edit (optional — saved to audit log)
            </label>
            <textarea name="edit_reason" id="edit_reason" class="form-input" style="height: 65px; padding: 10px 20px; resize: none; font-size: 13px;" placeholder="e.g. Corrected wrong amount, duplicate entry fixed...">{{ old('edit_reason') }}</textarea>
        </div>

        <div style="margin-top: 10px; display: flex; gap: 15px;">
            <button type="submit" class="btn-signin" style="margin: 0; width: auto;">Update Expense</button>
            <a href="{{ route('expenses.index') }}" class="btn-signin" style="margin: 0; width: auto; background: #718096; text-decoration: none; color: white; display: inline-flex; align-items: center; justify-content: center; padding: 0 20px;">Cancel</a>
        </div>
    </form>
</div>
@endsection
