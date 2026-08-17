<?php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\Expense;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\FleetDailyData;
use App\Models\PasgiAdvance;
use App\Models\PasgiAdjustment;
use App\Models\Maintenance;
use App\Models\DriverSalary;
use App\Models\EmployeeSalary;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->user()?->user_type === 'super_admin') {
            return redirect()->route('admin.roles.index');
        }

        // ─── Date Period Resolution ───────────────────────────────────────
        $period   = $request->get('period', 'all_time');
        $dateFrom = null;
        $dateTo   = null;

        switch ($period) {
            case 'today':
                $dateFrom = Carbon::today()->toDateString();
                $dateTo   = Carbon::today()->toDateString();
                break;
            case 'this_week':
                $dateFrom = Carbon::now()->startOfWeek()->toDateString();
                $dateTo   = Carbon::now()->toDateString();
                break;
            case 'this_month':
                $dateFrom = Carbon::now()->startOfMonth()->toDateString();
                $dateTo   = Carbon::now()->toDateString();
                break;
            case 'prev_month':
                $dateFrom = Carbon::now()->subMonthNoOverflow()->startOfMonth()->toDateString();
                $dateTo   = Carbon::now()->subMonthNoOverflow()->endOfMonth()->toDateString();
                break;
            case 'custom':
                $dateFrom = $request->get('date_from');
                $dateTo   = $request->get('date_to', Carbon::today()->toDateString());
                break;
            default: // all_time
                $dateFrom = null;
                $dateTo   = null;
        }

        // ─── Stat Cards ───────────────────────────────────────────────────
        $totalIncome   = Income::when($dateFrom, fn($q) => $q->whereDate('date', '>=', $dateFrom))
                               ->when($dateTo,   fn($q) => $q->whereDate('date', '<=', $dateTo))
                               ->sum('amount');

        $totalExpenses = Expense::when($dateFrom, fn($q) => $q->whereDate('date', '>=', $dateFrom))
                                ->when($dateTo,   fn($q) => $q->whereDate('date', '<=', $dateTo))
                                ->sum('amount');

        $netProfit = $totalIncome - $totalExpenses;

        $activeVehiclesCount = Vehicle::where('status', 'active')->count();
        $totalVehiclesCount  = Vehicle::count();

        // New KPIs
        $totalDiesel = FleetDailyData::when($dateFrom, fn($q) => $q->whereDate('date', '>=', $dateFrom))
                                     ->when($dateTo,   fn($q) => $q->whereDate('date', '<=', $dateTo))
                                     ->sum('daily_diesel_amount');

        $totalMaintenance = Maintenance::when($dateFrom, fn($q) => $q->whereDate('maintenance_date', '>=', $dateFrom))
                                       ->when($dateTo,   fn($q) => $q->whereDate('maintenance_date', '<=', $dateTo))
                                       ->sum('amount');

        $totalDriverSalaries   = DriverSalary::when($dateFrom, fn($q) => $q->whereDate('salary_period', '>=', $dateFrom))
                                              ->when($dateTo,   fn($q) => $q->whereDate('salary_period', '<=', $dateTo))
                                              ->sum('net_payable');
        $totalEmployeeSalaries = EmployeeSalary::when($dateFrom, fn($q) => $q->whereDate('salary_period', '>=', $dateFrom))
                                                ->when($dateTo,   fn($q) => $q->whereDate('salary_period', '<=', $dateTo))
                                                ->sum('net_payable');
        $totalSalaries = $totalDriverSalaries + $totalEmployeeSalaries;

        $totalPasgiGiven     = PasgiAdvance::sum('amount');
        $totalPasgiRecovered = PasgiAdjustment::sum('amount');
        $pasgiOutstanding    = max(0, $totalPasgiGiven - $totalPasgiRecovered);

        $totalKm = FleetDailyData::when($dateFrom, fn($q) => $q->whereDate('date', '>=', $dateFrom))
                                  ->when($dateTo,   fn($q) => $q->whereDate('date', '<=', $dateTo))
                                  ->sum('total_km');

        // ─── Fleet Operations Table (always today) ────────────────────────
        $todayStr        = Carbon::now()->toDateString();
        $fleetOperations = Vehicle::with(['driver', 'dailyData' => function ($query) use ($todayStr) {
            $query->where('date', $todayStr);
        }])->get()->map(function ($vehicle) {
            $todayLog = $vehicle->dailyData->first();
            return [
                'vehicle_number'    => $vehicle->vehicle_number,
                'registration_name' => $vehicle->registration_name,
                'driver_name'       => $vehicle->driver ? $vehicle->driver->name : 'N/A',
                'driver_code'       => $vehicle->driver ? 'DRV-' . str_pad($vehicle->driver->id, 3, '0', STR_PAD_LEFT) : 'N/A',
                'main_km'           => $todayLog ? $todayLog->main_km   : 0,
                'local_km'          => $todayLog ? $todayLog->local_km  : 0,
                'total_km'          => $todayLog ? $todayLog->total_km  : 0,
                'diesel_liters'     => $todayLog ? $todayLog->daily_diesel_liters : 0,
                'status'            => $vehicle->status,
                'efficiency'        => $todayLog && $todayLog->daily_diesel_liters > 0
                    ? min(100, round(($todayLog->total_km / ($todayLog->daily_diesel_liters * 6)) * 100))
                    : 0,
            ];
        });

        // ─── Chart Data — last 6 months (always all-time for trend) ──────
        $months      = [];
        $incomeData  = [];
        $expenseData = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthDate     = Carbon::now()->subMonths($i);
            $months[]      = $monthDate->format('M');
            $year          = $monthDate->year;
            $monthVal      = $monthDate->month;
            $incomeData[]  = (float) Income::whereYear('date', $year)->whereMonth('date', $monthVal)->sum('amount');
            $expenseData[] = (float) Expense::whereYear('date', $year)->whereMonth('date', $monthVal)->sum('amount');
        }

        // ─── Alerts ───────────────────────────────────────────────────────
        $alerts = [];

        $lowMileageLogs = FleetDailyData::with('vehicle')->where('date', $todayStr)->get();
        foreach ($lowMileageLogs as $log) {
            if ($log->daily_diesel_liters > 0) {
                $mileage = $log->total_km / $log->daily_diesel_liters;
                if ($mileage < 3.5) {
                    $alerts[] = ['type' => 'danger', 'title' => "Low mileage: {$log->vehicle->vehicle_number} (" . round($mileage, 1) . " KM/L)", 'time' => 'Today'];
                }
            }
        }
        $recentPasgi = PasgiAdvance::with('driver')->where('date', $todayStr)->orderBy('id', 'desc')->take(3)->get();
        foreach ($recentPasgi as $pasgi) {
            $alerts[] = ['type' => 'success', 'title' => "Rs. " . number_format($pasgi->amount) . " Pasgi → {$pasgi->driver->name}", 'time' => 'Today'];
        }
        $recentMaintenances = Maintenance::with('vehicle')->orderBy('maintenance_date', 'desc')->take(2)->get();
        foreach ($recentMaintenances as $maint) {
            $alerts[] = ['type' => 'warning', 'title' => "Maintenance: {$maint->maintenance_type} — {$maint->vehicle->vehicle_number}", 'time' => Carbon::parse($maint->maintenance_date)->diffForHumans()];
        }
        if (count($alerts) === 0) {
            $alerts = [
                ['type' => 'info',    'title' => 'System online and operational', 'time' => 'Just now'],
                ['type' => 'success', 'title' => 'All fleet data up to date',     'time' => '5m ago'],
            ];
        }

        return view('dashboard', compact(
            'totalIncome', 'totalExpenses', 'netProfit',
            'activeVehiclesCount', 'totalVehiclesCount',
            'totalDiesel', 'totalMaintenance', 'totalSalaries',
            'pasgiOutstanding', 'totalKm',
            'fleetOperations', 'months', 'incomeData', 'expenseData', 'alerts',
            'period', 'dateFrom', 'dateTo'
        ));
    }
}


