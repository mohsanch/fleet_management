@extends('layouts.app')

@section('title', 'Pasgi Balance — ' . $driver->name)
@section('breadcrumbs', 'People / Pasgi Advances / Driver Balance')
@section('page_title', 'Pasgi Balance — ' . $driver->name)

@section('content')
<div style="max-width: 900px; margin: 0 auto; display: flex; flex-direction: column; gap: 24px;">

    {{-- Driver Summary --}}
    <div class="card" style="padding: 24px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="width: 52px; height: 52px; border-radius: 50%; background: rgba(99,179,237,0.15); display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="user" style="width: 22px; height: 22px; color: #63B3ED;"></i>
                </div>
                <div>
                    <div style="font-size: 18px; font-weight: 700; color: var(--text-primary);">{{ $driver->name }}</div>
                    <div style="font-size: 12px; color: var(--text-muted);">{{ $driver->phone ?? 'No phone' }} &bull; DRV-{{ str_pad($driver->id, 3, '0', STR_PAD_LEFT) }}</div>
                </div>
            </div>
            <div style="display: flex; gap: 20px; flex-wrap: wrap; align-items: center;">
                <div style="text-align: center;">
                    <div style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Total Given</div>
                    <div style="font-size: 20px; font-weight: 700; color: #E53E3E;">Rs. {{ number_format($totalGiven) }}</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Recovered</div>
                    <div style="font-size: 20px; font-weight: 700; color: #38A169;">Rs. {{ number_format($totalRecovered) }}</div>
                </div>
                <div style="text-align: center; padding: 10px 20px; background: {{ $outstanding > 0 ? 'rgba(229,62,62,0.1)' : 'rgba(56,161,105,0.1)' }}; border-radius: 12px;">
                    <div style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Outstanding</div>
                    <div style="font-size: 24px; font-weight: 800; color: {{ $outstanding > 0 ? '#E53E3E' : '#38A169' }};">Rs. {{ number_format($outstanding) }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Record Recovery Form --}}
    @can('add-transactions')
    <div class="card" style="padding: 24px;">
        <div class="chart-header" style="margin-bottom: 20px;">
            <div class="chart-title-block">
                <span class="chart-title">Record Pasgi Recovery</span>
                <span class="chart-subtitle">Deduct recovered amount from {{ $driver->name }}'s outstanding balance</span>
            </div>
        </div>
        <form action="{{ route('pasgi-advances.store-adjustment') }}" method="POST" class="auth-form" style="max-width: 100%; gap: 16px;">
            @csrf
            <input type="hidden" name="driver_id" value="{{ $driver->id }}">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label>Recovery Amount (Rs.)</label>
                    <input type="number" step="0.01" name="amount" class="form-input" placeholder="e.g. 5000"
                           value="{{ old('amount') }}" required min="1">
                    @error('amount') <span class="form-error">{{ $message }}</span> @enderror
                    @if($outstanding > 0)
                        <span style="font-size: 11px; color: var(--text-muted);">Outstanding balance: Rs. {{ number_format($outstanding) }}</span>
                    @else
                        <span style="font-size: 11px; color: #38A169;">✓ No outstanding balance.</span>
                    @endif
                </div>
                <div class="form-group">
                    <label>Recovery Date</label>
                    <input type="date" name="date" class="form-input" value="{{ old('date', date('Y-m-d')) }}" required>
                    @error('date') <span class="form-error">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="form-group">
                <label>Remarks (optional)</label>
                <textarea name="remarks" class="form-input" style="height: 60px; padding: 10px 16px; resize: none;">{{ old('remarks') }}</textarea>
            </div>
            <div style="display: flex; gap: 12px; margin-top: 4px;">
                <button type="submit" class="btn-signin" style="margin: 0; width: auto; background: #38A169;">
                    <i data-lucide="check-circle" style="width: 14px; height: 14px; display: inline; margin-right: 6px;"></i>
                    Record Recovery
                </button>
                <a href="{{ route('pasgi-advances.index') }}" class="btn-signin" style="margin: 0; width: auto; background: #718096; text-decoration: none; color: white; display: inline-flex; align-items: center; justify-content: center; padding: 0 20px;">Back to List</a>
            </div>
        </form>
    </div>
    @endcan

    {{-- Advances Given History --}}
    <div class="card" style="padding: 0; overflow: hidden;">
        <div style="padding: 18px 24px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between;">
            <span class="chart-title">Advances Given ({{ $advances->count() }})</span>
            <span style="font-weight: 700; color: #E53E3E;">Total: Rs. {{ number_format($totalGiven) }}</span>
        </div>
        <div class="table-responsive">
            <table class="purity-table">
                <thead><tr><th>Date</th><th>Amount</th><th>Remarks</th></tr></thead>
                <tbody>
                    @forelse($advances as $adv)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($adv->date)->format('d M Y') }}</td>
                        <td style="color: #E53E3E; font-weight: 600;">Rs. {{ number_format($adv->amount) }}</td>
                        <td style="color: var(--text-muted);">{{ $adv->remarks ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" style="text-align:center; color:var(--text-muted); padding: 20px;">No advances found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recovery History --}}
    <div class="card" style="padding: 0; overflow: hidden;">
        <div style="padding: 18px 24px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between;">
            <span class="chart-title">Recovery History ({{ $adjustments->count() }})</span>
            <span style="font-weight: 700; color: #38A169;">Recovered: Rs. {{ number_format($totalRecovered) }}</span>
        </div>
        <div class="table-responsive">
            <table class="purity-table">
                <thead><tr><th>Date</th><th>Amount Recovered</th><th>Remarks</th></tr></thead>
                <tbody>
                    @forelse($adjustments as $adj)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($adj->date)->format('d M Y') }}</td>
                        <td style="color: #38A169; font-weight: 600;">Rs. {{ number_format($adj->amount) }}</td>
                        <td style="color: var(--text-muted);">{{ $adj->remarks ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" style="text-align:center; color:var(--text-muted); padding: 20px;">No recoveries recorded</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
