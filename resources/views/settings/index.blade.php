@extends('layouts.app')

@section('title', 'Global Settings')
@section('breadcrumbs', 'Settings')
@section('page_title', 'System Settings')

@section('content')

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="chart-header" style="margin-bottom: 24px;">
        <div class="chart-title-block">
            <span class="chart-title">Global Fleet Parameters</span>
            <span class="chart-subtitle">Modify settings that govern calculations and operational locks</span>
        </div>
    </div>

    <form action="{{ route('settings.update') }}" method="POST" class="auth-form" style="max-width: 100%; gap: 20px;">
        @csrf

        <!-- Fuel Rate Parameter -->
        <div class="form-group">
            <label for="fuel_price_per_liter">Diesel Retail Price (Per Liter - Rs.)</label>
            <input type="number" step="0.01" name="fuel_price_per_liter" id="fuel_price_per_liter" class="form-input" 
                   value="{{ old('fuel_price_per_liter', $settings['fuel_price_per_liter'] ?? '') }}" required>
            <span style="font-size: 11px; color: var(--text-muted);">Used to calculate fuel refueling costs when only liters are entered.</span>
            @error('fuel_price_per_liter')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <!-- Maintenance Interval Parameter -->
        <div class="form-group">
            <label for="maintenance_interval_km">Default Maintenance Threshold (KM)</label>
            <input type="number" name="maintenance_interval_km" id="maintenance_interval_km" class="form-input" 
                   value="{{ old('maintenance_interval_km', $settings['maintenance_interval_km'] ?? '') }}" required>
            <span style="font-size: 11px; color: var(--text-muted);">Triggers a warning when a vehicle exceeds this mileage limit since last tuning.</span>
            @error('maintenance_interval_km')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <!-- Lock Cutoff Days Parameter -->
        <div class="form-group">
            <label for="lock_cutoff_days">Data Locking Duration (Days)</label>
            <input type="number" name="lock_cutoff_days" id="lock_cutoff_days" class="form-input" 
                   value="{{ old('lock_cutoff_days', $settings['lock_cutoff_days'] ?? '') }}" required>
            <span style="font-size: 11px; color: var(--text-muted);">Restricts data entry personnel from editing logs older than this duration.</span>
            @error('lock_cutoff_days')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div style="margin-top: 10px;">
            <button type="submit" class="btn-signin" style="margin: 0;">Save Parameters</button>
        </div>
    </form>
</div>
@endsection
