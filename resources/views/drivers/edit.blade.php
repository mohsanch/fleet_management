@extends('layouts.app')

@section('title', 'Edit Driver')
@section('breadcrumbs', 'Drivers / Edit')
@section('page_title', 'Edit Driver')

@section('content')
<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="chart-header" style="margin-bottom: 24px;">
        <div class="chart-title-block">
            <span class="chart-title">Modify Driver Profile</span>
            <span class="chart-subtitle">Update driver profile details and base salaries</span>
        </div>
        <a href="{{ route('drivers.index') }}" style="font-size: 11px; font-weight: 700; color: var(--primary); text-decoration: none;">Back to List</a>
    </div>

    <form action="{{ route('drivers.update', $driver->id) }}" method="POST" class="auth-form" enctype="multipart/form-data" style="max-width: 100%; gap: 18px;">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name">Driver's Name</label>
            <input type="text" name="name" id="name" class="form-input" value="{{ old('name', $driver->name) }}" required>
            @error('name')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="license_number">Driving License Number</label>
            <input type="text" name="license_number" id="license_number" class="form-input" value="{{ old('license_number', $driver->license_number) }}" required>
            @error('license_number')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="text" name="phone" id="phone" class="form-input" value="{{ old('phone', $driver->phone) }}" required>
            @error('phone')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="base_salary">Base Salary (Per Month)</label>
            <input type="number" name="base_salary" id="base_salary" class="form-input" value="{{ old('base_salary', $driver->base_salary) }}" required>
            @error('base_salary')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="status">Operational Status</label>
            <select name="status" id="status" class="form-input" style="height: 48px; padding: 10px 20px;" required>
                <option value="active" {{ old('status', $driver->status) === 'active' ? 'selected' : '' }}>Active (Available for assignments)</option>
                <option value="inactive" {{ old('status', $driver->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="suspended" {{ old('status', $driver->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
            </select>
            @error('status')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        
        @if($driver->attachments->count() > 0)
        <div class="form-group" style="margin-bottom: 20px;">
            <label>Current Attachments</label>
            <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 8px;">
                @foreach($driver->attachments as $attachment)
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

        <div style="margin-top: 10px;">
            <button type="submit" class="btn-signin" style="margin: 0;">Update Driver Profile</button>
        </div>
    </form>
</div>
@endsection
