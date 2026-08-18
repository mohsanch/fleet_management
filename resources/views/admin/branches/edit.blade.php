@extends('layouts.app')

@section('title', 'Edit Branch')
@section('breadcrumbs', 'Admin / Branches / Edit')
@section('page_title', 'Edit Branch')

@section('content')
<div class="card" style="max-width: 600px; margin: 0 auto; padding: 24px;">
    <h3 style="margin-top: 0; margin-bottom: 20px; color: var(--text-color);">Edit Branch</h3>

    <form action="{{ route('admin.branches.update', $branch->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div style="margin-bottom: 16px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-color);">Branch Name</label>
            <input type="text" name="name" class="form-input" required value="{{ old('name', $branch->name) }}" placeholder="e.g. Sahiwal Branch" style="width: 100%;">
            @error('name')
                <span style="color: #E53E3E; font-size: 12px; display: block; margin-top: 5px;">{{ $message }}</span>
            @enderror
        </div>

        <div style="margin-bottom: 16px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-color);">Branch Code</label>
            <input type="text" name="code" class="form-input" required value="{{ old('code', $branch->code) }}" placeholder="e.g. SWL" style="width: 100%;">
            @error('code')
                <span style="color: #E53E3E; font-size: 12px; display: block; margin-top: 5px;">{{ $message }}</span>
            @enderror
        </div>

        <div style="margin-bottom: 24px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-color);">Status</label>
            <select name="is_active" class="form-input" style="height: 48px; padding: 10px 20px;">
                <option value="1" {{ old('is_active', $branch->is_active ? '1' : '0') == '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ old('is_active', $branch->is_active ? '1' : '0') == '0' ? 'selected' : '' }}>Inactive</option>
            </select>
            @error('is_active')
                <span style="color: #E53E3E; font-size: 12px; display: block; margin-top: 5px;">{{ $message }}</span>
            @enderror
        </div>

        <div style="display: flex; gap: 12px; justify-content: flex-end;">
            <a href="{{ route('admin.branches.index') }}" class="btn-signin" style="background-color: #718096; text-decoration: none; text-align: center; line-height: 20px;">Cancel</a>
            <button type="submit" class="btn-signin">Update Branch</button>
        </div>
    </form>
</div>
@endsection
