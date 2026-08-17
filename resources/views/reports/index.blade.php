@extends('layouts.app')

@section('title', 'Reports')
@section('breadcrumbs', 'Reports')
@section('page_title', 'Reports')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    {{-- Report Selector Card --}}
    <div class="card" style="padding: 28px;">
        <div class="chart-header" style="margin-bottom: 24px;">
            <div class="chart-title-block">
                <span class="chart-title">Generate Report</span>
                <span class="chart-subtitle">Select a report type, apply filters, then generate, export as PDF or Excel</span>
            </div>
        </div>

        <form id="report-form" method="GET" action="{{ route('reports.generate') }}" style="display: flex; flex-direction: column; gap: 20px;">

            {{-- Report Type Grid --}}
            <div>
                <label style="font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 12px;">Report Type</label>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px;">
                    @foreach($reportTypes as $key => $label)
                    <label style="display: flex; align-items: center; gap: 10px; padding: 12px 14px; border-radius: 10px; border: 1.5px solid var(--border-color); cursor: pointer; transition: all 0.2s;" class="report-type-option">
                        <input type="radio" name="report_type" value="{{ $key }}" style="accent-color: var(--accent);" {{ old('report_type') === $key ? 'checked' : '' }}>
                        <span style="font-size: 13px; font-weight: 500; color: var(--text-primary);">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Date Range --}}
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 16px; align-items: end;">
                <div class="form-group" style="margin: 0;">
                    <label>Date From</label>
                    <input type="date" name="date_from" class="form-input" value="{{ old('date_from', \Carbon\Carbon::now()->startOfMonth()->toDateString()) }}">
                </div>
                <div class="form-group" style="margin: 0;">
                    <label>Date To</label>
                    <input type="date" name="date_to" class="form-input" value="{{ old('date_to', \Carbon\Carbon::now()->toDateString()) }}">
                </div>
                <div class="form-group" style="margin: 0;">
                    <label>Vehicle (optional)</label>
                    <select name="vehicle_id" class="form-input" style="height: 48px; padding: 10px 14px;">
                        <option value="">All Vehicles</option>
                        @foreach($vehicles as $v)
                        <option value="{{ $v->id }}" {{ old('vehicle_id') == $v->id ? 'selected' : '' }}>{{ $v->vehicle_number }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin: 0;">
                    <label>Driver (optional)</label>
                    <select name="driver_id" class="form-input" style="height: 48px; padding: 10px 14px;">
                        <option value="">All Drivers</option>
                        @foreach($drivers as $d)
                        <option value="{{ $d->id }}" {{ old('driver_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Actions --}}
            <div style="display: flex; gap: 12px; padding-top: 4px; border-top: 1px solid var(--border-color);">
                <button type="submit" class="btn-signin" style="margin: 0; width: auto; display: inline-flex; align-items: center; gap: 8px;">
                    <i data-lucide="bar-chart-2" style="width: 15px; height: 15px;"></i>
                    Generate Report
                </button>
                <button type="button" onclick="exportReport('pdf')" class="btn-signin" style="margin: 0; width: auto; display: inline-flex; align-items: center; gap: 8px; background: #E53E3E;">
                    <i data-lucide="file-text" style="width: 15px; height: 15px;"></i>
                    Export PDF
                </button>
                <button type="button" onclick="exportReport('excel')" class="btn-signin" style="margin: 0; width: auto; display: inline-flex; align-items: center; gap: 8px; background: #38A169;">
                    <i data-lucide="table-2" style="width: 15px; height: 15px;"></i>
                    Export Excel
                </button>
            </div>
        </form>
    </div>

</div>

<style>
.report-type-option:has(input:checked) {
    border-color: var(--accent);
    background: rgba(var(--accent-rgb, 66,153,225), 0.08);
}
.report-type-option:hover {
    border-color: var(--accent);
}
</style>

<script>
function exportReport(type) {
    const form = document.getElementById('report-form');
    const url = new URL(type === 'pdf' ? '{{ route("reports.export-pdf") }}' : '{{ route("reports.export-excel") }}', window.location.origin);
    const data = new FormData(form);
    for (const [key, val] of data.entries()) {
        if (val) url.searchParams.set(key, val);
    }
    window.open(url.toString(), '_blank');
}
</script>
@endsection
