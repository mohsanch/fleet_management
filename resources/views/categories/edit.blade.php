@extends('layouts.app')

@section('title', 'Edit Category')
@section('breadcrumbs', 'Categories / Edit')
@section('page_title', 'Edit Category')

@section('content')
<div class="card" style="max-width: 500px; margin: 0 auto;">
    <div class="chart-header" style="margin-bottom: 24px;">
        <div class="chart-title-block">
            <span class="chart-title">Edit Category Profile</span>
            <span class="chart-subtitle">Modify the settings for this transaction category</span>
        </div>
    </div>

    <form action="{{ route('categories.update', $category->id) }}" method="POST" class="auth-form" style="max-width: 100%; gap: 20px;">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name">Category Name</label>
            <input type="text" name="name" id="name" class="form-input" value="{{ old('name', $category->name) }}" required>
            @error('name')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="type">Category Type</label>
            <select name="type" id="type" class="form-input" style="height: 48px; padding: 10px 20px;" required>
                <option value="income" {{ old('type', $category->type) === 'income' ? 'selected' : '' }}>Income (Inflow/Earnings)</option>
                <option value="expense" {{ old('type', $category->type) === 'expense' ? 'selected' : '' }}>Expense (Outflow/Costs)</option>
                <option value="maintenance" {{ old('type', $category->type) === 'maintenance' ? 'selected' : '' }}>Maintenance (Repairs/Services)</option>
                <option value="general" {{ old('type', $category->type) === 'general' ? 'selected' : '' }}>General (Other operations)</option>
            </select>
            @error('type')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="is_active">Status</label>
            <select name="is_active" id="is_active" class="form-input" style="height: 48px; padding: 10px 20px;" required>
                <option value="1" {{ old('is_active', $category->is_active) ? 'selected' : '' }}>Active (Available for transactions)</option>
                <option value="0" {{ !old('is_active', $category->is_active) ? 'selected' : '' }}>Inactive (Hidden from dropdowns)</option>
            </select>
            @error('is_active')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div style="margin-top: 10px; display: flex; gap: 15px;">
            <button type="submit" class="btn-signin" style="margin: 0; width: auto;">Update Category</button>
            <a href="{{ route('categories.index') }}" class="btn-signin" style="margin: 0; width: auto; background: #718096; text-decoration: none; color: white; display: inline-flex; align-items: center; justify-content: center; padding: 0 20px;">Cancel</a>
        </div>
    </form>
</div>
@endsection
