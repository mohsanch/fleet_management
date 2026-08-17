@extends('layouts.app')

@section('title', 'Register Vehicle')
@section('breadcrumbs', 'Vehicles / Register')
@section('page_title', 'Register Vehicle')

@section('content')
<div class="card" style="max-width: 550px; margin: 0 auto;">
    <div class="chart-header" style="margin-bottom: 24px;">
        <div class="chart-title-block">
            <span class="chart-title">New Vehicle Profile</span>
            <span class="chart-subtitle">Create a record for a fleet vehicle</span>
        </div>
    </div>

    <form action="{{ route('vehicles.store') }}" method="POST" class="auth-form" enctype="multipart/form-data" style="max-width: 100%; gap: 20px;">
        @csrf

        <div class="form-group">
            <label for="vehicle_number">Vehicle License Plate Number</label>
            <input type="text" name="vehicle_number" id="vehicle_number" class="form-input" placeholder="e.g. LES-1122, MN-556" value="{{ old('vehicle_number') }}" required>
            @error('vehicle_number')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="registration_name">Registration Owner Name</label>
            <input type="text" name="registration_name" id="registration_name" class="form-input" placeholder="e.g. Al-Fatah Transport Ltd" value="{{ old('registration_name') }}">
            @error('registration_name')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="type">Vehicle Type / Model</label>
            <input type="text" name="type" id="type" class="form-input" placeholder="e.g. Mazda Truck, Hino Bus, Loader" value="{{ old('type') }}">
            @error('type')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="assigned_driver_id">Assigned Default Driver</label>
            <select name="assigned_driver_id" id="assigned_driver_id" class="form-input" style="height: 48px; padding: 10px 20px;">
                <option value="">No Driver Assigned</option>
                @foreach($drivers as $driver)
                    <option value="{{ $driver->id }}" {{ old('assigned_driver_id') == $driver->id ? 'selected' : '' }}>
                        {{ $driver->name }} (License: {{ $driver->license_number }})
                    </option>
                @endforeach
            </select>
            @error('assigned_driver_id')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="status">Operational Status</label>
            <select name="status" id="status" class="form-input" style="height: 48px; padding: 10px 20px;" required>
                <option value="active" {{ old('status') === 'active' || old('status') === null ? 'selected' : '' }}>Active (Available for assignments)</option>
                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive / Out of Order</option>
            </select>
            @error('status')
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
            <button type="submit" class="btn-signin" style="margin: 0; width: auto;">Save Vehicle</button>
            <a href="{{ route('vehicles.index') }}" class="btn-signin" style="margin: 0; width: auto; background: #718096; text-decoration: none; color: white; display: inline-flex; align-items: center; justify-content: center; padding: 0 20px;">Cancel</a>
        </div>
    </form>
</div>
@endsection
