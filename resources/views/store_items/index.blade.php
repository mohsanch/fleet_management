@extends('layouts.app')

@section('title', 'Store Inventory Items')
@section('breadcrumbs', 'Store Items')
@section('page_title', 'Store Inventory Items')

@section('content')
<div class="card table-card">
    <div class="chart-header" style="display: flex; flex-direction: column; align-items: flex-start; gap: 16px; width: 100%; border-bottom: none; padding-bottom: 0;">
        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; flex-wrap: wrap; gap: 15px;">
            <div class="chart-title-block">
                <span class="chart-title">Spare Parts & Store Inventory</span>
                <span class="chart-subtitle">View and record vehicle parts purchased, tools, and materials</span>
            </div>
            @can('store.create')
            <a href="{{ route('store-items.create') }}" class="btn-signin" style="padding: 10px 18px; width: auto; display: inline-flex; align-items: center; gap: 8px; font-size: 12px; margin: 0; white-space: nowrap;">
                <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                <span>Add Store Item</span>
            </a>
            @endcan
        </div>
        <div style="width: 100%; display: flex; justify-content: flex-start; align-items: center; background: rgba(0,0,0,0.02); padding: 12px 16px; border-radius: 8px; border: 1px solid var(--border-color); box-shadow: inset 0 1px 2px rgba(0,0,0,0.02);">
            <form action="{{ route('store-items.index') }}" method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap; margin: 0; width: 100%;">
                <input type="text" name="search" class="form-input" placeholder="Search keyword..." value="{{ request('search') }}" style="height: 38px; padding: 5px 15px; font-size: 13px; max-width: 200px; margin: 0;">
                <div style="display: flex; align-items: center; gap: 5px;">
                    <span style="font-size: 11px; color: var(--text-muted);">From:</span>
                    <input type="date" name="date_from" class="form-input" value="{{ request('date_from') }}" style="height: 38px; padding: 5px 10px; font-size: 13px; width: auto; margin: 0;">
                </div>
                <div style="display: flex; align-items: center; gap: 5px;">
                    <span style="font-size: 11px; color: var(--text-muted);">To:</span>
                    <input type="date" name="date_to" class="form-input" value="{{ request('date_to') }}" style="height: 38px; padding: 5px 10px; font-size: 13px; width: auto; margin: 0;">
                </div>
                <button type="submit" class="btn-signin" style="padding: 0 20px; height: 38px; margin: 0; width: auto; font-size: 12px; background: var(--primary); border-color: var(--primary);">Filter</button>
                @if(request('search') || request('date_from') || request('date_to'))
                    <a href="{{ route('store-items.index') }}" class="btn-signin" style="padding: 10px 20px; height: 38px; margin: 0; width: auto; font-size: 12px; background: #718096; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; color: #fff;">Clear</a>
                @endif
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="purity-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Item Name</th>
                    <th>Qty</th>
                    <th>Vehicle Associated</th>
                    <th>Vendor</th>
                    <th>Remarks</th>
                    <th>Amount</th>
                    @canany(['store.edit','store.delete'])
                    <th style="text-align: right;">Actions</th>
                    @endcanany
                </tr>
            </thead>
            <tbody>
                @forelse($storeItems as $item)
                <tr>
                    <td>
                        <strong style="color: var(--text-color);">{{ \Carbon\Carbon::parse($item->date)->format('M d, Y') }}</strong>
                    </td>
                    <td>
                        <strong style="color: var(--text-color);">{{ $item->item_name }}</strong>
                    </td>
                    <td>
                        <span style="font-weight: 700; color: var(--primary);">{{ $item->quantity }}</span>
                    </td>
                    <td>
                        @if($item->vehicle)
                            <strong style="color: var(--primary);">{{ $item->vehicle->vehicle_number }}</strong>
                        @else
                            <span class="badge warning" style="font-size: 9px;">General Store</span>
                        @endif
                    </td>
                    <td>
                        <span style="font-weight: 700; color: var(--text-color);">{{ $item->vendor }}</span>
                    </td>
                    <td>
                        <span style="color: var(--text-muted); font-size: 12px;">{{ Str::limit($item->remarks, 40) }}</span>
                    </td>
                    <td>
                        <strong style="color: #E53E3E; font-size: 15px;">Rs. {{ number_format($item->amount) }}</strong>
                    </td>
                    @canany(['store.edit','store.delete'])
                    <td style="text-align: right;">
                        <div style="display: inline-flex; gap: 15px; align-items: center;">
                            @can('store.edit')
                                <a href="{{ route('store-items.edit', $item->id) }}" style="color: var(--primary); text-decoration: none; font-weight: 700; font-size: 12px;">Edit</a>
                            @endcan
                            @can('store.delete')
                                <form action="{{ route('store-items.destroy', $item->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="delete-btn" style="background: none; border: none; color: #E53E3E; cursor: pointer; font-weight: 700; font-size: 12px; padding: 0;">Delete</button>
                                </form>
                            @endcan
                        </div>
                    </td>
                    @endcanany
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 30px;">No store inventory items found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div style="margin-top: 15px;">
        {{ $storeItems->links() }}
    </div>
</div>
@endsection
