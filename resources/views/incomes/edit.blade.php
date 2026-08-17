@extends('layouts.app')

@section('title', 'Edit Income')
@section('breadcrumbs', 'Financials / Incomes / Edit')
@section('page_title', 'Edit Income Record')

@section('content')
<div class="card" style="max-width: 500px; margin: 0 auto;">
    <div class="chart-header" style="margin-bottom: 24px;">
        <div class="chart-title-block">
            <span class="chart-title">Edit Income Record</span>
            <span class="chart-subtitle">Modify the details of this income transaction</span>
        </div>
    </div>

    <form action="{{ route('incomes.update', $income->id) }}" method="POST" class="auth-form" style="max-width: 100%; gap: 20px;">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="date">Date</label>
            <input type="date" name="date" id="date" class="form-input" value="{{ old('date', $income->date) }}" required>
            @error('date')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="category_id">Select Category</label>
            <select name="category_id" id="category_id" class="form-input" style="height: 48px; padding: 10px 20px;" required>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id', $income->category_id) == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            @error('category_id')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="reference_source">Source / Client Name</label>
            <input type="text" name="reference_source" id="reference_source" class="form-input" value="{{ old('reference_source', $income->reference_source) }}">
            @error('reference_source')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="amount">Income Amount (Rs.)</label>
            <input type="number" step="0.01" name="amount" id="amount" class="form-input" value="{{ old('amount', $income->amount) }}" required>
            @error('amount')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="description">Description / Invoice Reference</label>
            <textarea name="description" id="description" class="form-input" style="height: 85px; padding: 10px 20px; resize: none;">{{ old('description', $income->description) }}</textarea>
            @error('description')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group" style="border-top: 1px solid var(--border-color); padding-top: 18px; margin-top: 8px;">
            <label for="edit_reason" style="color: var(--text-muted); font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">
                <i data-lucide="file-text" style="width: 12px; height: 12px; display: inline; margin-right: 4px;"></i>
                Reason for Edit (optional — saved to audit log)
            </label>
            <textarea name="edit_reason" id="edit_reason" class="form-input" style="height: 65px; padding: 10px 20px; resize: none; font-size: 13px;" placeholder="e.g. Corrected wrong amount, duplicate entry fixed...">{{ old('edit_reason') }}</textarea>
        </div>

        <div style="margin-top: 10px; display: flex; gap: 15px;">
            <button type="submit" class="btn-signin" style="margin: 0; width: auto;">Update Income</button>
            <a href="{{ route('incomes.index') }}" class="btn-signin" style="margin: 0; width: auto; background: #718096; text-decoration: none; color: white; display: inline-flex; align-items: center; justify-content: center; padding: 0 20px;">Cancel</a>
        </div>
    </form>
</div>
@endsection
