<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} — Fleet Management</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1a202c; background: #fff; }

        .header { background: #2D3748; color: white; padding: 18px 24px; margin-bottom: 20px; }
        .header h1 { font-size: 18px; font-weight: 700; margin-bottom: 4px; }
        .header .meta { font-size: 10px; opacity: 0.7; }

        .summary-row { display: flex; gap: 12px; margin-bottom: 16px; flex-wrap: wrap; }
        .summary-card { flex: 1; min-width: 140px; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px 14px; }
        .summary-card .label { font-size: 9px; color: #718096; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px; }
        .summary-card .value { font-size: 16px; font-weight: 700; color: #2d3748; }

        .content { padding: 0 24px 24px; }

        table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        thead th { background: #2D3748; color: white; padding: 8px 10px; text-align: left; font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; }
        tbody tr:nth-child(even) { background: #f7fafc; }
        tbody td { padding: 7px 10px; border-bottom: 1px solid #e2e8f0; font-size: 10px; color: #2d3748; }
        tbody tr:last-child td { border-bottom: none; }

        .profit-row { background: #f0fff4 !important; font-weight: 700; }
        .loss-row   { background: #fff5f5 !important; font-weight: 700; }
        .income-row { color: #276749; }
        .expense-row { color: #718096; }

        .footer { margin-top: 20px; padding: 10px 24px; border-top: 1px solid #e2e8f0; font-size: 9px; color: #a0aec0; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <div class="meta">
            Fleet Management System &nbsp;|&nbsp;
            @if($dateFrom && $dateTo)
                Period: {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} – {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
            @else
                All Time
            @endif
            &nbsp;|&nbsp; Generated: {{ now()->format('d M Y, h:i A') }}
        </div>
    </div>

    <div class="content">

        {{-- Summary Cards --}}
        @if(!empty($summary))
        <div class="summary-row">
            @foreach($summary as $label => $value)
            <div class="summary-card">
                <div class="label">{{ $label }}</div>
                <div class="value">{{ $value }}</div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Data Table --}}
        @if($data->isNotEmpty())
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    @foreach($columns as $col)
                    <th>{{ $col['label'] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($data as $i => $row)
                <tr class="{{ isset($row['type']) ? $row['type'].'-row' : '' }}">
                    <td>{{ $i + 1 }}</td>
                    @foreach($columns as $col)
                    <td>{{ $row[$col['key']] ?? '—' }}</td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p style="color: #718096; text-align: center; padding: 30px 0;">No data found for the selected period.</p>
        @endif

    </div>

    <div class="footer">
        Fleet Management System &bull; Confidential &bull; {{ $data->count() }} records &bull; Printed {{ now()->format('d M Y') }}
    </div>
</body>
</html>
