@extends('layouts.app')

@section('title', $title)
@section('breadcrumbs', 'Reports / ' . $title)
@section('page_title', $title)

@section('content')
<div style="display: flex; flex-direction: column; gap: 20px;">

    {{-- Filter Summary Bar --}}
    <div class="card" style="padding: 14px 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
            <span style="font-size: 13px; font-weight: 600; color: var(--text-primary);">{{ $title }}</span>
            @if($dateFrom || $dateTo)
            <span style="font-size: 12px; color: var(--text-muted); background: rgba(255,255,255,0.05); padding: 4px 10px; border-radius: 6px;">
                {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d M Y') : 'All' }}
                —
                {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('d M Y') : 'Today' }}
            </span>
            @else
            <span style="font-size: 12px; color: var(--text-muted);">All Time</span>
            @endif
            <span style="font-size: 12px; color: var(--text-muted);">{{ $data->count() }} records</span>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('reports.index') }}" style="padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; border: 1.5px solid var(--border-color); color: var(--text-muted); text-decoration: none;">← Back</a>
            <a href="{{ route('reports.export-pdf', request()->all()) }}" target="_blank"
               style="padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; background: #E53E3E; color: #fff; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                <i data-lucide="file-text" style="width: 13px; height: 13px;"></i> PDF
            </a>
            <a href="{{ route('reports.export-excel', request()->all()) }}"
               style="padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; background: #38A169; color: #fff; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                <i data-lucide="table-2" style="width: 13px; height: 13px;"></i> Excel
            </a>
            <button onclick="window.print()" style="padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; background: #4A5568; color: #fff; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                <i data-lucide="printer" style="width: 13px; height: 13px;"></i> Print
            </button>
        </div>
    </div>

    {{-- Summary KPI Row --}}
    @if(!empty($summary))
    <div style="display: flex; gap: 16px; flex-wrap: wrap;">
        @foreach($summary as $label => $value)
        <div class="card" style="padding: 16px 22px; flex: 1; min-width: 180px;">
            <div style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">{{ $label }}</div>
            <div style="font-size: 20px; font-weight: 700; color: var(--text-primary);">{{ $value }}</div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Data Table --}}
    <div class="card" style="padding: 0; overflow: hidden;">
        @if($data->isEmpty())
        <div style="padding: 48px; text-align: center; color: var(--text-muted);">
            <i data-lucide="inbox" style="width: 40px; height: 40px; opacity: 0.3; display: block; margin: 0 auto 12px;"></i>
            No data found for the selected filters.
        </div>
        @else
        <div class="table-responsive">
            <table class="purity-table" id="report-table">
                <thead>
                    <tr>
                        @foreach($columns as $col)
                        <th>{{ $col['label'] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $row)
                    <tr @if(isset($row['type']) && $row['type'] === 'profit') style="background: rgba(56,161,105,0.08); font-weight: 700;"
                        @elseif(isset($row['type']) && $row['type'] === 'loss') style="background: rgba(229,62,62,0.08); font-weight: 700;"
                        @elseif(isset($row['type']) && $row['type'] === 'income') style="color: #38A169;"
                        @elseif(isset($row['type']) && $row['type'] === 'expense') style="color: var(--text-muted);"
                        @endif>
                        @foreach($columns as $col)
                        <td>{{ $row[$col['key']] ?? '—' }}</td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

</div>

<style>
@media print {
    .sidebar, .topbar, .card:first-child, .btn-signin, a[href] { display: none !important; }
    body { background: white !important; }
    .data-table { font-size: 11px; }
}
</style>
@endsection
