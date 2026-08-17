@extends('layouts.app')

@section('title', 'Issue Pasgi Advance')
@section('breadcrumbs', 'Payroll / Pasgi Advances / Issue')
@section('page_title', 'Issue Pasgi Advance')

@section('content')
<div class="card" style="max-width: 500px; margin: 0 auto;">
    <div class="chart-header" style="margin-bottom: 24px;">
        <div class="chart-title-block">
            <span class="chart-title">New Pasgi Advance</span>
            <span class="chart-subtitle">Issue a cash advance to a driver — tracked against their salary</span>
        </div>
    </div>

    <form action="{{ route('pasgi-advances.store') }}" method="POST" class="auth-form" style="max-width: 100%; gap: 20px;">
        @csrf

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="date">Date</label>
                <input type="date" name="date" id="date" class="form-input" value="{{ old('date', date('Y-m-d')) }}" required>
                @error('date') <span class="form-error">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="amount">Amount (Rs.)</label>
                <input type="number" step="0.01" name="amount" id="amount" class="form-input" placeholder="e.g. 5000" value="{{ old('amount') }}" required>
                @error('amount') <span class="form-error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="form-group">
            <label for="driver_id">Select Driver</label>
            <select name="driver_id" id="driver_id" class="form-input" style="height: 48px; padding: 10px 20px;" required>
                <option value="" disabled selected>Select driver</option>
                @foreach($drivers as $driver)
                    <option value="{{ $driver->id }}" {{ old('driver_id') == $driver->id ? 'selected' : '' }}>
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
                    <option value="{{ $vehicle->id }}" {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                        {{ $vehicle->vehicle_number }}
                    </option>
                @endforeach
            </select>
            @error('vehicle_id') <span class="form-error">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label for="remarks">Remarks / Reason</label>
            <textarea name="remarks" id="remarks" class="form-input" placeholder="Reason for advance, e.g. Diesel costs, emergency..." style="height: 80px; padding: 10px 20px; resize: none;">{{ old('remarks') }}</textarea>
            @error('remarks') <span class="form-error">{{ $message }}</span> @enderror
        </div>

        <div style="margin-top: 10px; display: flex; gap: 15px;">
            <button type="submit" class="btn-signin" style="margin: 0; width: auto;">Issue Advance</button>
            <a href="{{ route('pasgi-advances.index') }}" class="btn-signin" style="margin: 0; width: auto; background: #718096; text-decoration: none; color: white; display: inline-flex; align-items: center; justify-content: center; padding: 0 20px;">Cancel</a>
        </div>
    </form>
</div>
@endsection
