@extends('layouts.app')

@section('title', 'Log Daily Activity')
@section('breadcrumbs', 'Daily Data / Log')
@section('page_title', 'Log Daily Activity')

@section('content')
<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="chart-header" style="margin-bottom: 24px;">
        <div class="chart-title-block">
            <span class="chart-title">New Operational Record</span>
            <span class="chart-subtitle">Record daily runs, fuel metrics, and driver advance for a vehicle</span>
        </div>
    </div>

    <form action="{{ route('daily-data.store') }}" method="POST" class="auth-form" enctype="multipart/form-data" style="max-width: 100%; gap: 20px;">
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
                <label for="pasgi_given">Driver Pasgi Advance (Rs.)</label>
                <input type="number" step="0.01" name="pasgi_given" id="pasgi_given" class="form-input" placeholder="e.g. 1500" value="{{ old('pasgi_given', 0) }}" required>
                @error('pasgi_given')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="vehicle_id">Select Vehicle</label>
                <select name="vehicle_id" id="vehicle_id" class="form-input" style="height: 48px; padding: 10px 20px;" required>
                    <option value="" disabled selected>Select vehicle</option>
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}" {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                            {{ $vehicle->vehicle_number }} ({{ $vehicle->type }})
                        </option>
                    @endforeach
                </select>
                @error('vehicle_id')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="driver_id">Select Driver</label>
                <select name="driver_id" id="driver_id" class="form-input" style="height: 48px; padding: 10px 20px;" required>
                    <option value="" disabled selected>Select driver</option>
                    @foreach($drivers as $driver)
                        <option value="{{ $driver->id }}" {{ old('driver_id') == $driver->id ? 'selected' : '' }}>
                            {{ $driver->name }} (License: {{ $driver->license_number }})
                        </option>
                    @endforeach
                </select>
                @error('driver_id')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px;">
            <div class="form-group">
                <label for="daily_diesel_liters">Diesel Liters Refueled</label>
                <input type="number" step="0.01" name="daily_diesel_liters" id="daily_diesel_liters" class="form-input" placeholder="e.g. 55.4" value="{{ old('daily_diesel_liters', 0) }}" required>
                @error('daily_diesel_liters')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="daily_diesel_amount">Diesel Total Amount (Rs.)</label>
                <input type="number" step="0.01" name="daily_diesel_amount" id="daily_diesel_amount" class="form-input" placeholder="Calculated automatically or custom" value="{{ old('daily_diesel_amount', 0) }}" required>
                @error('daily_diesel_amount')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px;">
            <div class="form-group">
                <label for="main_km">Main Highway Route Mileage (KM)</label>
                <input type="number" name="main_km" id="main_km" class="form-input" placeholder="e.g. 350" value="{{ old('main_km', 0) }}" required>
                @error('main_km')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="local_km">Local City Route Mileage (KM)</label>
                <input type="number" name="local_km" id="local_km" class="form-input" placeholder="e.g. 45" value="{{ old('local_km', 0) }}" required>
                @error('local_km')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="form-group">
            <label for="remarks">Remarks / Route Description</label>
            <textarea name="remarks" id="remarks" class="form-input" placeholder="Details about this operational log..." style="height: 80px; padding: 10px 20px; resize: none;">{{ old('remarks') }}</textarea>
            @error('remarks')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        
        <div class="form-group">
            <label for="attachment">Upload Attachment (PDF / Image)</label>
            <input type="file" name="attachment" id="attachment" class="form-input" style="padding: 10px 15px; height: auto;" onchange="previewAttachment(this)">
            @error('attachment')
                <span class="form-error">{{ $message }}</span>
            @enderror
            <div id="attachment-preview-container" style="display: none; margin-top: 15px; padding: 15px; background: rgba(0,0,0,0.02); border: 1px dashed var(--border-color); border-radius: 8px; align-items: center; gap: 15px;">
                <div id="attachment-preview-img-wrapper" style="display: none; border-radius: 4px; overflow: hidden; width: 60px; height: 60px; border: 1px solid var(--border-color);">
                    <img id="attachment-preview-img" src="" alt="Preview" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <div id="attachment-preview-file-icon-wrapper" style="display: none; align-items: center; justify-content: center; width: 60px; height: 60px; background: #EDF2F7; border-radius: 4px; color: #4A5568; font-size: 14px; font-weight: bold;">
                    DOC
                </div>
                <div style="flex-grow: 1;">
                    <div id="attachment-preview-filename" style="font-size: 13px; font-weight: 600; color: var(--text-color); margin-bottom: 2px; word-break: break-all;"></div>
                    <div id="attachment-preview-filesize" style="font-size: 11px; color: var(--text-muted);"></div>
                </div>
                <button type="button" class="btn-signin" style="background: #E53E3E; border-color: #E53E3E; padding: 5px 12px; height: auto; margin: 0; font-size: 11px; width: auto;" onclick="removeSelectedAttachment()">Remove</button>
            </div>
        </div>

        <div style="margin-top: 10px; display: flex; gap: 15px;">
            <button type="submit" class="btn-signin" style="margin: 0; width: auto;">Save Operational Log</button>
            <a href="{{ route('daily-data.index') }}" class="btn-signin" style="margin: 0; width: auto; background: #718096; text-decoration: none; color: white; display: inline-flex; align-items: center; justify-content: center; padding: 0 20px;">Cancel</a>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const fuelPrice = {{ \App\Models\Setting::where('key', 'fuel_price_per_liter')->value('value') ?: 0 }};
        
        // Auto-calculate Diesel Amount based on Liters input
        const litersInput = document.getElementById('daily_diesel_liters');
        const amountInput = document.getElementById('daily_diesel_amount');

        litersInput.addEventListener('input', function() {
            const liters = parseFloat(this.value) || 0;
            if (fuelPrice > 0) {
                amountInput.value = Math.round(liters * fuelPrice);
            }
        });
    });
</script>
@endsection
