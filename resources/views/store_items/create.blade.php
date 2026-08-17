@extends('layouts.app')

@section('title', 'Add Store Item')
@section('breadcrumbs', 'Store Items / Add')
@section('page_title', 'Add Store Item')

@section('content')
<div class="card" style="max-width: 550px; margin: 0 auto;">
    <div class="chart-header" style="margin-bottom: 24px;">
        <div class="chart-title-block">
            <span class="chart-title">New Store Purchase</span>
            <span class="chart-subtitle">Record tools, tires, or vehicle spare parts purchases in inventory</span>
        </div>
    </div>

    <form action="{{ route('store-items.store') }}" method="POST" class="auth-form" style="max-width: 100%; gap: 20px;">
        @csrf

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="date">Purchase Date</label>
                <input type="date" name="date" id="date" class="form-input" value="{{ old('date', date('Y-m-d')) }}" required>
                @error('date')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="amount">Purchase Cost (Rs.)</label>
                <input type="number" step="0.01" name="amount" id="amount" class="form-input" placeholder="e.g. 24000" value="{{ old('amount') }}" required>
                @error('amount')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="form-group">
            <label for="item_name">Spare Part / Item Name</label>
            <input type="text" name="item_name" id="item_name" class="form-input" placeholder="e.g. Bridgestone Tire, Cabin Filter" value="{{ old('item_name') }}" required>
            @error('item_name')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="quantity">Quantity purchased</label>
                <input type="number" name="quantity" id="quantity" class="form-input" placeholder="e.g. 4" value="{{ old('quantity', 1) }}" required>
                @error('quantity')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="vehicle_id">Assign to Vehicle (Optional)</label>
                <select name="vehicle_id" id="vehicle_id" class="form-input" style="height: 48px; padding: 10px 20px;">
                    <option value="">Keep in Store (Unassigned)</option>
                    @foreach($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}" {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                            {{ $vehicle->vehicle_number }}
                        </option>
                    @endforeach
                </select>
                @error('vehicle_id')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="form-group">
            <label for="vendor">Vendor / Supplier Name</label>
            <input type="text" name="vendor" id="vendor" class="form-input" placeholder="e.g. Saddar Parts Dealer, Hino Depot" value="{{ old('vendor') }}" required>
            @error('vendor')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="remarks">Remarks / Technical Specifications</label>
            <textarea name="remarks" id="remarks" class="form-input" placeholder="Enter specifications, warranties, or specific remarks..." style="height: 80px; padding: 10px 20px; resize: none;">{{ old('remarks') }}</textarea>
            @error('remarks')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div style="margin-top: 10px; display: flex; gap: 15px;">
            <button type="submit" class="btn-signin" style="margin: 0; width: auto;">Save Store Item</button>
            <a href="{{ route('store-items.index') }}" class="btn-signin" style="margin: 0; width: auto; background: #718096; text-decoration: none; color: white; display: inline-flex; align-items: center; justify-content: center; padding: 0 20px;">Cancel</a>
        </div>
    </form>
</div>
@endsection
