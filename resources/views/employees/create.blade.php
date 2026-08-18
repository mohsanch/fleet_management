@extends('layouts.app')

@section('title', 'Add New Employee')
@section('breadcrumbs', 'Employees / Create')
@section('page_title', 'Create Employee')

@section('content')
<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="chart-header" style="margin-bottom: 24px;">
        <div class="chart-title-block">
            <span class="chart-title">Register Employee Profile</span>
            <span class="chart-subtitle">Add a new yard, dispatch, or administrative staff profile</span>
        </div>
        <a href="{{ route('employees.index') }}" style="font-size: 11px; font-weight: 700; color: var(--primary); text-decoration: none;">Back to List</a>
    </div>

    <form action="{{ route('employees.store') }}" method="POST" class="auth-form" style="max-width: 100%; gap: 18px;">
        @csrf

        <div class="form-group">
            <label for="name">Employee Name</label>
            <input type="text" name="name" id="name" class="form-input" placeholder="Enter employee's full name" value="{{ old('name') }}" required>
            @error('name')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="designation">Designation (Job Title)</label>
            <input type="text" name="designation" id="designation" class="form-input" placeholder="e.g. Yard Manager, Dispatcher, Accountant" value="{{ old('designation') }}" required>
            @error('designation')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="contact">Contact Number (Optional)</label>
            <input type="text" name="contact" id="contact" class="form-input" placeholder="e.g. 0300-1234567" value="{{ old('contact') }}">
            @error('contact')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="base_salary">Base Salary (Per Month)</label>
            <input type="number" name="base_salary" id="base_salary" class="form-input" placeholder="e.g. 50000" value="{{ old('base_salary') }}" required>
            @error('base_salary')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="status">Employment Status</label>
            <select name="status" id="status" class="form-input" style="height: 48px; padding: 10px 20px;" required>
                <option value="active" {{ old('status') === 'active' || old('status') === null ? 'selected' : '' }}>Active (Currently employed)</option>
                <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive (Terminated or Resigned)</option>
            </select>
            @error('status')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        @if(auth()->user()->branch_id === null)
        <div class="form-group">
            <label for="branch_id">Assign Branch</label>
            <select name="branch_id" id="branch_id" class="form-input" style="height: 48px; padding: 10px 20px;">
                <option value="">None (Global)</option>
                @foreach(\App\Models\Branch::where('is_active', true)->get() as $b)
                    <option value="{{ $b->id }}" {{ old('branch_id') == $b->id ? 'selected' : '' }}>
                        {{ $b->name }} ({{ $b->code }})
                    </option>
                @endforeach
            </select>
            @error('branch_id')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>
        @endif

        <div style="margin-top: 10px;">
            <button type="submit" class="btn-signin" style="margin: 0;">Save Employee Profile</button>
        </div>
    </form>
</div>
@endsection
