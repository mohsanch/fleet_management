@extends('layouts.app')

@section('title', 'Edit Pasgi Advance')
@section('breadcrumbs', 'Payroll / Pasgi Advances / Edit')
@section('page_title', 'Edit Pasgi Advance')

@section('content')
<div class="card" style="max-width: 500px; margin: 0 auto;">
    <div class="chart-header" style="margin-bottom: 24px;">
        <div class="chart-title-block">
            <span class="chart-title">Edit Pasgi Advance</span>
            <span class="chart-subtitle">Modify this driver advance record</span>
        </div>
    </div>

    <form action="{{ route('pasgi-advances.update', $pasgiAdvance->id) }}" method="POST" class="auth-form" style="max-width: 100%; gap: 20px;">
        @csrf
        @method('PUT')

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="date">Date</label>
                <input type="date" name="date" id="date" class="form-input" value="{{ old('date', $pasgiAdvance->date) }}" required>
                @error('date') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="amount">Amount (Rs.)</label>
                <input type="number" step="0.01" name="amount" id="amount" class="form-input" value="{{ old('amount', $pasgiAdvance->amount) }}" required>
                @error('amount') <span class="form-error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group">
            <label for="driver_id">Select Driver</label>
            <select name="driver_id" id="driver_id" class="form-input" style="height: 48px; padding: 10px 20px;" required>
                @foreach($drivers as $driver)
                    <option value="{{ $driver->id }}" {{ old('driver_id', $pasgiAdvance->driver_id) == $driver->id ? 'selected' : '' }}>
                        {{ $driver->name }}
                    </option>
                @endforeach
            </select>
            @error('driver_id') <span class="form-error">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label for="vehicle_id">Associated Vehicle (Optional)</label>
            <select name="vehicle_id" id="vehicle_id" class="form-input" style="height: 48px; padding: 10px 20px;">
                <option value="">No Vehicle</option>
                @foreach($vehicles as $vehicle)
                    <option value="{{ $vehicle->id }}" {{ old('vehicle_id', $pasgiAdvance->vehicle_id) == $vehicle->id ? 'selected' : '' }}>
                        {{ $vehicle->vehicle_number }}
                    </option>
                @endforeach
            </select>
            @error('vehicle_id') <span class="form-error">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label for="remarks">Remarks / Reason</label>
            <textarea name="remarks" id="remarks" class="form-input" style="height: 80px; padding: 10px 20px; resize: none;">{{ old('remarks', $pasgiAdvance->remarks) }}</textarea>
            @error('remarks') <span class="form-error">{{ $message }}</span> @enderror
        </div>

        
        <div class="form-group" style="border-top: 1px solid var(--border-color); padding-top: 18px; margin-top: 8px;">
            <label for="edit_reason" style="color: var(--text-muted); font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                <i data-lucide="file-text" style="width: 12px; height: 12px; display: inline; margin-right: 4px;"></i>
                Reason for Edit (optional — saved to audit log)
            </label>
            <textarea name="edit_reason" id="edit_reason" class="form-input" style="height: 65px; padding: 10px 20px; resize: none; font-size: 13px;" placeholder="e.g. Corrected wrong amount, duplicate entry fixed...">{{ old('edit_reason') }}</textarea>
        </div>

        <div style="margin-top: 10px; display: flex; gap: 15px;">
            <button type="submit" class="btn-signin" style="margin: 0; width: auto;">Update Advance</button>
            <a href="{{ route('pasgi-advances.index') }}" class="btn-signin" style="margin: 0; width: auto; background: #718096; text-decoration: none; color: white; display: inline-flex; align-items: center; justify-content: center; padding: 0 20px;">Cancel</a>
        </div>
    </form>
</div>
@endsection
