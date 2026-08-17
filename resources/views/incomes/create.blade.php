@extends('layouts.app')

@section('title', 'Log Income')
@section('breadcrumbs', 'Financials / Incomes / Log')
@section('page_title', 'Log Income')

@section('content')
<div class="card" style="max-width: 500px; margin: 0 auto;">
    <div class="chart-header" style="margin-bottom: 24px;">
        <div class="chart-title-block">
            <span class="chart-title">New Income Record</span>
            <span class="chart-subtitle">Record earnings from cargo freight, billing, or sales</span>
        </div>
    </div>

    <form action="{{ route('incomes.store') }}" method="POST" class="auth-form" style="max-width: 100%; gap: 20px;">
        @csrf

        <div class="form-group">
            <label for="date">Date</label>
            <input type="date" name="date" id="date" class="form-input" value="{{ old('date', date('Y-m-d')) }}" required>
            @error('date')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="category_id">Select Category</label>
            <select name="category_id" id="category_id" class="form-input" style="height: 48px; padding: 10px 20px;" required>
                <option value="" disabled selected>Select category</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
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
            <input type="text" name="reference_source" id="reference_source" class="form-input" placeholder="e.g. Metro Trading Co, Cash Booking" value="{{ old('reference_source') }}">
            @error('reference_source')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="amount">Income Amount (Rs.)</label>
            <input type="number" step="0.01" name="amount" id="amount" class="form-input" placeholder="e.g. 75000" value="{{ old('amount') }}" required>
            @error('amount')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="description">Description / Invoice Reference</label>
            <textarea name="description" id="description" class="form-input" placeholder="Enter shipment details, route info, or bill references..." style="height: 85px; padding: 10px 20px; resize: none;">{{ old('description') }}</textarea>
            @error('description')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div style="margin-top: 10px; display: flex; gap: 15px;">
            <button type="submit" class="btn-signin" style="margin: 0; width: auto;">Save Income</button>
            <a href="{{ route('incomes.index') }}" class="btn-signin" style="margin: 0; width: auto; background: #718096; text-decoration: none; color: white; display: inline-flex; align-items: center; justify-content: center; padding: 0 20px;">Cancel</a>
        </div>
    </form>
</div>
@endsection
