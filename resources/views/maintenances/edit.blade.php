@extends('layouts.app')

@section('title', 'Edit Maintenance Log')
@section('breadcrumbs', 'Maintenance / Edit')
@section('page_title', 'Edit Maintenance Log')

@section('content')
<div class="card" style="max-width: 550px; margin: 0 auto;">
    <div class="chart-header" style="margin-bottom: 24px;">
        <div class="chart-title-block">
            <span class="chart-title">Edit Maintenance Record</span>
            <span class="chart-subtitle">Modify parameters for this service record</span>
        </div>
    </div>

    <form action="{{ route('maintenances.update', $maintenance->id) }}" method="POST" class="auth-form" enctype="multipart/form-data" style="max-width: 100%; gap: 20px;">
        @csrf
        @method('PUT')

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="maintenance_date">Maintenance Date</label>
                <input type="date" name="maintenance_date" id="maintenance_date" class="form-input" value="{{ old('maintenance_date', $maintenance->maintenance_date) }}" required>
                @error('maintenance_date')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="amount">Service Cost (Rs.)</label>
                <input type="number" step="0.01" name="amount" id="amount" class="form-input" value="{{ old('amount', $maintenance->amount) }}" required>
                @error('amount')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="form-group">
            <label for="vehicle_id">Select Vehicle</label>
            <select name="vehicle_id" id="vehicle_id" class="form-input" style="height: 48px; padding: 10px 20px;" required>
                @foreach($vehicles as $vehicle)
                    <option value="{{ $vehicle->id }}" {{ old('vehicle_id', $maintenance->vehicle_id) == $vehicle->id ? 'selected' : '' }}>
                        {{ $vehicle->vehicle_number }}
                    </option>
                @endforeach
            </select>
            @error('vehicle_id')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="maintenance_type">Service Type</label>
            <select name="maintenance_type" id="maintenance_type" class="form-input" style="height: 48px; padding: 10px 20px;" required>
                <option value="Mobile Oil" {{ old('maintenance_type', $maintenance->maintenance_type) === 'Mobile Oil' ? 'selected' : '' }}>Mobil Oil / Engine Oil Change</option>
                <option value="Filter" {{ old('maintenance_type', $maintenance->maintenance_type) === 'Filter' ? 'selected' : '' }}>Filter Replacement</option>
                <option value="Tires" {{ old('maintenance_type', $maintenance->maintenance_type) === 'Tires' ? 'selected' : '' }}>Tyre Service / Purchase</option>
                <option value="Brakes" {{ old('maintenance_type', $maintenance->maintenance_type) === 'Brakes' ? 'selected' : '' }}>Brake Repairs</option>
                <option value="Tuning" {{ old('maintenance_type', $maintenance->maintenance_type) === 'Tuning' ? 'selected' : '' }}>Engine Tuning / Overhaul</option>
                <option value="Others" {{ old('maintenance_type', $maintenance->maintenance_type) === 'Others' ? 'selected' : '' }}>Others</option>
            </select>
            @error('maintenance_type')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="vendor">Vendor / Workshop Name</label>
                <input type="text" name="vendor" id="vendor" class="form-input" value="{{ old('vendor', $maintenance->vendor) }}" required>
                @error('vendor')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="invoice_number">Invoice / Receipt No.</label>
                <input type="text" name="invoice_number" id="invoice_number" class="form-input" value="{{ old('invoice_number', $maintenance->invoice_number) }}">
                @error('invoice_number')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="form-group">
            <label for="remarks">Remarks / Description of Work</label>
            <textarea name="remarks" id="remarks" class="form-input" style="height: 80px; padding: 10px 20px; resize: none;">{{ old('remarks', $maintenance->remarks) }}</textarea>
            @error('remarks')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        
        @if($maintenance->attachments->count() > 0)
        <div class="form-group" style="margin-bottom: 20px;">
            <label>Current Attachments</label>
            <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 8px;">
                @foreach($maintenance->attachments as $attachment)
                <div style="display: flex; align-items: center; justify-content: space-between; background: var(--bg-light); border: 1px solid var(--border-color); border-radius: 8px; padding: 8px 12px;">
                    <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank" style="font-size: 13px; font-weight: 700; color: var(--primary); text-decoration: none; display: flex; align-items: center; gap: 6px;">
                        <i data-lucide="file" style="width: 14px; height: 14px;"></i>
                        {{ $attachment->file_name }}
                    </a>
                    <form action="{{ route('attachments.destroy', $attachment->id) }}" method="POST" style="margin: 0;" class="delete-attachment-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="background: none; border: none; color: #E53E3E; cursor: pointer; font-size: 12px; font-weight: 700; padding: 0;">Remove</button>
                    </form>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="form-group">
            <label for="attachment">Upload New Attachment (PDF / Image)</label>
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

        
        <div class="form-group" style="border-top: 1px solid var(--border-color); padding-top: 18px; margin-top: 8px;">
            <label for="edit_reason" style="color: var(--text-muted); font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                <i data-lucide="file-text" style="width: 12px; height: 12px; display: inline; margin-right: 4px;"></i>
                Reason for Edit (optional — saved to audit log)
            </label>
            <textarea name="edit_reason" id="edit_reason" class="form-input" style="height: 65px; padding: 10px 20px; resize: none; font-size: 13px;" placeholder="e.g. Corrected wrong amount, duplicate entry fixed...">{{ old('edit_reason') }}</textarea>
        </div>

        <div style="margin-top: 10px; display: flex; gap: 15px;">
            <button type="submit" class="btn-signin" style="margin: 0; width: auto;">Update Record</button>
            <a href="{{ route('maintenances.index') }}" class="btn-signin" style="margin: 0; width: auto; background: #718096; text-decoration: none; color: white; display: inline-flex; align-items: center; justify-content: center; padding: 0 20px;">Cancel</a>
        </div>
    </form>
</div>
@endsection
