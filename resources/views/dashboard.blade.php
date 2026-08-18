@extends('layouts.app')

@section('title', 'Fleet Dashboard')
@section('breadcrumbs', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')

{{-- ─── Date Period Filter Bar ─────────────────────────────────────────── --}}
<div class="card" style="margin-bottom: 24px; padding: 14px 20px;">
    <form method="GET" action="{{ route('dashboard') }}" id="period-form" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
        <span style="font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap;">Period:</span>
        @foreach([
            'all_time'   => 'All Time',
            'today'      => 'Today',
            'this_week'  => 'This Week',
            'this_month' => 'This Month',
            'prev_month' => 'Prev Month',
            'custom'     => 'Custom',
        ] as $key => $label)
        <button type="submit" name="period" value="{{ $key }}"
            style="padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; border: 1.5px solid {{ $period === $key ? 'var(--accent)' : 'var(--border-color)' }}; background: {{ $period === $key ? 'var(--accent)' : 'transparent' }}; color: {{ $period === $key ? '#fff' : 'var(--text-muted)' }}; cursor: pointer; transition: all 0.2s; white-space: nowrap;">
            {{ $label }}
        </button>
        @endforeach

        {{-- Custom date range --}}
        <div id="custom-range" style="display: {{ $period === 'custom' ? 'flex' : 'none' }}; align-items: center; gap: 8px; flex-wrap: wrap;">
            <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-input" style="height: 34px; padding: 4px 10px; font-size: 12px; width: 140px;">
            <span style="color: var(--text-muted); font-size: 12px;">to</span>
            <input type="date" name="date_to" value="{{ $dateTo }}" class="form-input" style="height: 34px; padding: 4px 10px; font-size: 12px; width: 140px;">
            <button type="submit" name="period" value="custom" style="padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; border: none; background: var(--accent); color: #fff; cursor: pointer;">Apply</button>
        </div>

        @if($dateFrom || $dateTo)
        <span style="font-size: 11px; color: var(--text-muted); margin-left: 4px;">
            @if($dateFrom && $dateTo) {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} – {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
            @elseif($dateFrom) From {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }}
            @endif
        </span>
        @endif
    </form>
</div>
<script>
    document.querySelectorAll('button[value="custom"]').forEach(function(btn) {
        if (btn.form && btn.getAttribute('name') === 'period') {
            btn.addEventListener('click', function(e) {
                var customDiv = document.getElementById('custom-range');
                if (customDiv) customDiv.style.display = 'flex';
            });
        }
    });
    // Show custom range if period is custom on load
    @if($period === 'custom')
    document.getElementById('custom-range').style.display = 'flex';
    @endif
</script>

{{-- ─── Stats Cards Grid ────────────────────────────────────────────────── --}}
<div class="stats-grid">
    @can('incomes.view')
    {{-- Total Income --}}
    <div class="card stat-card">
        <div class="stat-info">
            <span class="stat-label">Total Income</span>
            <div class="stat-value-container">
                <span class="stat-value">Rs. {{ number_format($totalIncome) }}</span>
                <span class="stat-trend up">Revenue</span>
            </div>
        </div>
        <div class="stat-icon-box"><i data-lucide="trending-up"></i></div>
    </div>
    @endcan

    @can('expenses.view')
    {{-- Total Expenses --}}
    <div class="card stat-card">
        <div class="stat-info">
            <span class="stat-label">Total Expenses</span>
            <div class="stat-value-container">
                <span class="stat-value">Rs. {{ number_format($totalExpenses) }}</span>
                <span class="stat-trend down">{{ $totalIncome > 0 ? round(($totalExpenses / $totalIncome) * 100, 1) : '0' }}% of Inc</span>
            </div>
        </div>
        <div class="stat-icon-box"><i data-lucide="credit-card"></i></div>
    </div>
    @endcan

    @if(auth()->user()->can('incomes.view') && auth()->user()->can('expenses.view'))
    {{-- Net Profit --}}
    <div class="card stat-card">
        <div class="stat-info">
            <span class="stat-label">Net Profit / Loss</span>
            <div class="stat-value-container">
                <span class="stat-value">Rs. {{ number_format($netProfit) }}</span>
                <span class="stat-trend {{ $netProfit >= 0 ? 'up' : 'down' }}">{{ $netProfit >= 0 ? 'Profit' : 'Loss' }}</span>
            </div>
        </div>
        <div class="stat-icon-box"><i data-lucide="award"></i></div>
    </div>
    @endif

    @can('expenses.view')
    {{-- Total Diesel --}}
    <div class="card stat-card">
        <div class="stat-info">
            <span class="stat-label">Diesel Spend</span>
            <div class="stat-value-container">
                <span class="stat-value">Rs. {{ number_format($totalDiesel) }}</span>
                <span class="stat-trend" style="color: #ED8936;">Fuel Cost</span>
            </div>
        </div>
        <div class="stat-icon-box" style="background: rgba(237,137,54,0.15);"><i data-lucide="droplets" style="color: #ED8936;"></i></div>
    </div>

    {{-- Total Maintenance --}}
    <div class="card stat-card">
        <div class="stat-info">
            <span class="stat-label">Maintenance Cost</span>
            <div class="stat-value-container">
                <span class="stat-value">Rs. {{ number_format($totalMaintenance) }}</span>
                <span class="stat-trend" style="color: #E53E3E;">Repairs</span>
            </div>
        </div>
        <div class="stat-icon-box" style="background: rgba(229,62,62,0.12);"><i data-lucide="wrench" style="color: #E53E3E;"></i></div>
    </div>

    {{-- Total Salaries --}}
    <div class="card stat-card">
        <div class="stat-info">
            <span class="stat-label">Salaries Paid</span>
            <div class="stat-value-container">
                <span class="stat-value">Rs. {{ number_format($totalSalaries) }}</span>
                <span class="stat-trend" style="color: #805AD5;">Staff Cost</span>
            </div>
        </div>
        <div class="stat-icon-box" style="background: rgba(128,90,213,0.12);"><i data-lucide="users" style="color: #805AD5;"></i></div>
    </div>
    @endcan

    {{-- Active Vehicles --}}
    <div class="card stat-card">
        <div class="stat-info">
            <span class="stat-label">Active Vehicles</span>
            <div class="stat-value-container">
                <span class="stat-value">{{ $activeVehiclesCount }} / {{ $totalVehiclesCount }}</span>
                <span class="stat-trend text-muted" style="font-size: 11px; font-weight: 500;">
                    {{ $totalVehiclesCount > 0 ? round(($activeVehiclesCount / $totalVehiclesCount) * 100) : 0 }}% Operational
                </span>
            </div>
        </div>
        <div class="stat-icon-box"><i data-lucide="truck"></i></div>
    </div>

    @can('expenses.view')
    {{-- Pasgi Outstanding --}}
    <div class="card stat-card">
        <div class="stat-info">
            <span class="stat-label">Pasgi Outstanding</span>
            <div class="stat-value-container">
                <span class="stat-value">Rs. {{ number_format($pasgiOutstanding) }}</span>
                <span class="stat-trend down">Pending Recovery</span>
            </div>
        </div>
        <div class="stat-icon-box" style="background: rgba(214,158,46,0.12);"><i data-lucide="wallet" style="color: #D69E2E;"></i></div>
    </div>

    {{-- Total KM --}}
    <div class="card stat-card">
        <div class="stat-info">
            <span class="stat-label">Total KM Covered</span>
            <div class="stat-value-container">
                <span class="stat-value">{{ number_format($totalKm) }} km</span>
                <span class="stat-trend up">Fleet Distance</span>
            </div>
        </div>
        <div class="stat-icon-box" style="background: rgba(49,151,149,0.12);"><i data-lucide="map" style="color: #319795;"></i></div>
    </div>
    @endcan
</div>


<!-- Charts Section -->
<div class="charts-grid" style="{{ auth()->user()->can('incomes.view') ? '' : 'grid-template-columns: 1fr;' }}">
    @can('incomes.view')
    <!-- Sales Overview / Profit & Expense Chart (Dark Card) -->
    <div class="card chart-card chart-card-dark">
        <div class="chart-header">
            <div class="chart-title-block">
                <span class="chart-title">Financial Performance Overview</span>
                <span class="chart-subtitle">Monthly income vs expenses (calculated from database)</span>
            </div>
        </div>
        <div class="chart-canvas-container">
            <div id="financialChart" style="height: 100%;"></div>
        </div>
        
        <div class="chart-stats">
            <div class="chart-stat-item">
                <span class="chart-stat-label">
                    <span class="chart-stat-indicator" style="background-color: #4FD1C5;"></span>
                    Total Income
                </span>
                <span class="chart-stat-val">Rs. {{ number_format($totalIncome / 1000, 0) }}k</span>
            </div>
            <div class="chart-stat-item">
                <span class="chart-stat-label">
                    <span class="chart-stat-indicator" style="background-color: #F8F9FA;"></span>
                    Total Expenses
                </span>
                <span class="chart-stat-val">Rs. {{ number_format($totalExpenses / 1000, 0) }}k</span>
            </div>
            <div class="chart-stat-item">
                <span class="chart-stat-label">
                    <span class="chart-stat-indicator" style="background-color: #38A169;"></span>
                    Net Profit
                </span>
                <span class="chart-stat-val">Rs. {{ number_format($netProfit / 1000, 0) }}k</span>
            </div>
            <div class="chart-stat-item">
                <span class="chart-stat-label">
                    <span class="chart-stat-indicator" style="background-color: #319795;"></span>
                    Total Vehicles
                </span>
                <span class="chart-stat-val">{{ $totalVehiclesCount }} Units</span>
            </div>
        </div>
    </div>
    @endcan

    <!-- Quick Operations & Add Buttons -->
    <div class="card quick-actions-card">
        <div class="chart-header">
            <div class="chart-title-block">
                <span class="chart-title">Quick Actions</span>
                <span class="chart-subtitle">Log new operations data instantly</span>
            </div>
        </div>
        
        <div class="action-buttons-list">
            @php $hasAnyCreate = false; @endphp
            
            @can('daily-data.create')
                @php $hasAnyCreate = true; @endphp
                <a href="{{ route('daily-data.create') }}" class="btn-action">
                    <span>Add Daily Operational Log</span>
                    <div class="btn-action-icon">
                        <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                    </div>
                </a>
            @endcan
            
            @can('maintenance.create')
                @php $hasAnyCreate = true; @endphp
                <a href="{{ route('maintenances.create') }}" class="btn-action">
                    <span>Log Maintenance Issue</span>
                    <div class="btn-action-icon">
                        <i data-lucide="wrench" style="width: 14px; height: 14px;"></i>
                    </div>
                </a>
            @endcan
            
            @can('payroll.create')
                @php $hasAnyCreate = true; @endphp
                <a href="{{ route('driver-salaries.create') }}" class="btn-action">
                    <span>Disburse Driver Salary</span>
                    <div class="btn-action-icon">
                        <i data-lucide="dollar-sign" style="width: 14px; height: 14px;"></i>
                    </div>
                </a>
            @endcan
            
            @can('advances.create')
                @php $hasAnyCreate = true; @endphp
                <a href="{{ route('pasgi-advances.create') }}" class="btn-action">
                    <span>Disburse Driver Pasgi</span>
                    <div class="btn-action-icon">
                        <i data-lucide="arrow-up-right" style="width: 14px; height: 14px;"></i>
                    </div>
                </a>
            @endcan
            
            @can('expenses.create')
                @php $hasAnyCreate = true; @endphp
                <a href="{{ route('expenses.create') }}" class="btn-action">
                    <span>Record General Expense</span>
                    <div class="btn-action-icon">
                        <i data-lucide="minus" style="width: 14px; height: 14px;"></i>
                    </div>
                </a>
            @endcan
            
            @if(!$hasAnyCreate)
                <div style="padding: 15px; color: var(--text-muted); font-size: 13px; text-align: center;">
                    You do not have permission to log new transaction data.
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Detailed Data Tables & Alerts -->
<div class="content-grid">
    <!-- Active Fleet Tracker Table -->
    <div class="card table-card">
        <div class="chart-header">
            <div class="chart-title-block">
                <span class="chart-title">Active Fleet Operations</span>
                <span class="chart-subtitle">Daily operational logs and fuel usage tracker</span>
            </div>
            <a href="{{ route('daily-data.index') }}" style="font-size: 11px; font-weight: 700; color: var(--primary); text-decoration: none;">View All Logs</a>
        </div>
        
        <div class="table-responsive">
            <table class="purity-table">
                <thead>
                    <tr>
                        <th>Vehicle Details</th>
                        <th>Assigned Driver</th>
                        <th>Daily KM</th>
                        <th>Diesel Liters</th>
                        <th>Status</th>
                        <th>Target Efficiency</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fleetOperations as $op)
                    <tr>
                        <td>
                            <div class="item-profile">
                                <div class="avatar-icon">{{ substr($op['vehicle_number'], 0, 2) }}</div>
                                <div class="item-details">
                                    <span class="item-title">{{ $op['vehicle_number'] }}</span>
                                    <span class="item-subtitle">{{ $op['registration_name'] }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="item-details">
                                <span class="item-title">{{ $op['driver_name'] }}</span>
                                <span class="item-subtitle">ID: {{ $op['driver_code'] }}</span>
                            </div>
                        </td>
                        <td>
                            <strong>{{ number_format($op['total_km']) }} KM</strong>
                            <div style="font-size: 9px; color: var(--text-muted);">
                                Main: {{ $op['main_km'] }} | Local: {{ $op['local_km'] }}
                            </div>
                        </td>
                        <td>{{ number_format($op['diesel_liters'], 1) }} Liters</td>
                        <td>
                            <span class="badge {{ $op['status'] === 'active' ? 'active' : 'warning' }}">
                                {{ $op['status'] }}
                            </span>
                        </td>
                        <td>
                            <div class="progress-container">
                                <span class="progress-text">{{ $op['efficiency'] }}%</span>
                                <div class="progress-track">
                                    <div class="progress-bar" style="width: {{ $op['efficiency'] }}%;"></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--text-muted);">No operational fleet logs found for today.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Timeline Alerts -->
    <div class="card timeline-card">
        <div class="chart-header">
            <div class="chart-title-block">
                <span class="chart-title">Fleet Activities & Alerts</span>
                <span class="chart-subtitle">Real-time alerts and logs</span>
            </div>
        </div>
        
        <div class="timeline">
            @foreach($alerts as $alert)
            <div class="timeline-item">
                <span class="timeline-dot {{ $alert['type'] }}"></span>
                <div class="timeline-content">
                    <span class="timeline-title">{{ $alert['title'] }}</span>
                    <span class="timeline-time">{{ $alert['time'] }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@section('scripts')
@can('incomes.view')
<!-- ApexCharts CDN -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Multi-series Area Chart (Income vs Expense)
        var options = {
            chart: {
                height: 220,
                type: 'area',
                toolbar: {
                    show: false
                },
                foreColor: '#A0AEC0',
                fontFamily: 'Plus Jakarta Sans, sans-serif'
            },
            colors: ['#4FD1C5', '#F8F9FA'],
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            series: [{
                name: 'Income',
                data: @json($incomeData)
            }, {
                name: 'Expenses',
                data: @json($expenseData)
            }],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0.1,
                    stops: [0, 90, 100]
                }
            },
            grid: {
                borderColor: 'rgba(255, 255, 255, 0.1)',
                strokeDashArray: 5,
                xaxis: {
                    lines: {
                        show: true
                    }
                },
                yaxis: {
                    lines: {
                        show: true
                    }
                }
            },
            xaxis: {
                categories: @json($months),
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false
                }
            },
            yaxis: {
                labels: {
                    formatter: function (value) {
                        return "Rs. " + Math.round(value).toLocaleString();
                    }
                }
            },
            tooltip: {
                theme: 'dark',
                y: {
                    formatter: function (value) {
                        return "Rs. " + Math.round(value).toLocaleString();
                    }
                }
            },
            legend: {
                show: false
            }
        };

        var chart = new ApexCharts(document.querySelector("#financialChart"), options);
        chart.render();
    });
</script>
@endcan
@endsection
