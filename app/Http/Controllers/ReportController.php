<?php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\Expense;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\FleetDailyData;
use App\Models\Maintenance;
use App\Models\DriverSalary;
use App\Models\EmployeeSalary;
use App\Models\PasgiAdvance;
use App\Models\PasgiAdjustment;
use App\Models\StoreItem;
use App\Models\Category;
use App\Exports\ReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{
    // ── Report type definitions ──────────────────────────────────────────
    private array $reportTypes = [
        'income'              => 'Income Report',
        'expense'             => 'Expense Report',
        'vehicle_expense'     => 'Vehicle-wise Expense',
        'vehicle_maintenance' => 'Vehicle-wise Maintenance',
        'diesel'              => 'Diesel Consumption Report',
        'daily_km'            => 'Daily KM Report',
        'driver_salary'       => 'Driver Salary Report',
        'driver_fine'         => 'Driver Fine Report',
        'pasgi_advance'       => 'Driver Pasgi/Advance Report',
        'store_expense'       => 'Store Expense Report',
        'office_expense'      => 'Office Expense Report',
        'profit_loss'         => 'Profit & Loss Report',
    ];

    public function index()
    {
        Gate::authorize('view-financials');
        $vehicles = Vehicle::orderBy('vehicle_number')->get();
        $drivers  = Driver::orderBy('name')->get();
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('reports.index', [
            'reportTypes' => $this->reportTypes,
            'vehicles'    => $vehicles,
            'drivers'     => $drivers,
            'categories'  => $categories,
        ]);
    }

    public function generate(Request $request)
    {
        Gate::authorize('view-financials');
        $request->validate([
            'report_type' => ['required', 'in:' . implode(',', array_keys($this->reportTypes))],
            'date_from'   => ['nullable', 'date'],
            'date_to'     => ['nullable', 'date'],
        ]);

        [$data, $columns, $summary] = $this->buildReport($request);

        $title     = $this->reportTypes[$request->report_type];
        $dateFrom  = $request->date_from;
        $dateTo    = $request->date_to;

        return view('reports.result', compact('data', 'columns', 'summary', 'title', 'dateFrom', 'dateTo') + [
            'request'     => $request->all(),
            'reportTypes' => $this->reportTypes,
        ]);
    }

    public function exportPdf(Request $request)
    {
        Gate::authorize('view-financials');
        [$data, $columns, $summary] = $this->buildReport($request);
        $title    = $this->reportTypes[$request->report_type] ?? 'Report';
        $dateFrom = $request->date_from;
        $dateTo   = $request->date_to;

        $pdf = Pdf::loadView('reports.pdf', compact('data', 'columns', 'summary', 'title', 'dateFrom', 'dateTo'))
                  ->setPaper('a4', 'landscape');

        return $pdf->download(str_replace(' ', '_', $title) . '_' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        Gate::authorize('view-financials');
        [$data, $columns, $summary] = $this->buildReport($request);
        $title = $this->reportTypes[$request->report_type] ?? 'Report';

        // Convert collection to array of plain arrays for Excel
        $rows = $data->map(function ($row) use ($columns) {
            return array_values(array_map(fn($col) => $row[$col['key']] ?? '', $columns));
        });

        $headings = array_map(fn($col) => $col['label'], $columns);
        $export   = new ReportExport(collect($rows), $headings, $title);

        return Excel::download($export, str_replace(' ', '_', $title) . '_' . now()->format('Y-m-d') . '.xlsx');
    }

    // ── Core report builder ───────────────────────────────────────────────
    private function buildReport(Request $request): array
    {
        $from = $request->date_from;
        $to   = $request->date_to;

        $scope = function ($q) use ($from, $to, $request) {
            if ($from) $q->whereDate($request->has('use_maint_date') ? 'maintenance_date' : 'date', '>=', $from);
            if ($to)   $q->whereDate($request->has('use_maint_date') ? 'maintenance_date' : 'date', '<=', $to);
        };

        $vehicleId = $request->vehicle_id;
        $driverId  = $request->driver_id;

        switch ($request->report_type) {

            // ── 1. Income ─────────────────────────────────────────────────
            case 'income':
                $rows = Income::with('category')
                    ->when($from, fn($q) => $q->whereDate('date', '>=', $from))
                    ->when($to,   fn($q) => $q->whereDate('date', '<=', $to))
                    ->when($request->category_id, fn($q) => $q->where('category_id', $request->category_id))
                    ->orderBy('date', 'desc')->get()
                    ->map(fn($r) => [
                        'date'     => $r->date,
                        'category' => $r->category->name ?? '—',
                        'source'   => $r->reference_source ?? '—',
                        'amount'   => 'Rs. ' . number_format($r->amount),
                        'description' => $r->description ?? '—',
                        '_amount_raw' => $r->amount,
                    ]);
                $columns = [
                    ['key'=>'date','label'=>'Date'],['key'=>'category','label'=>'Category'],
                    ['key'=>'source','label'=>'Source'],['key'=>'amount','label'=>'Amount'],
                    ['key'=>'description','label'=>'Description'],
                ];
                $summary = ['Total' => 'Rs. ' . number_format($rows->sum('_amount_raw'))];
                break;

            // ── 2. Expense ────────────────────────────────────────────────
            case 'expense':
                $rows = Expense::with(['category', 'vehicle'])
                    ->when($from, fn($q) => $q->whereDate('date', '>=', $from))
                    ->when($to,   fn($q) => $q->whereDate('date', '<=', $to))
                    ->when($vehicleId, fn($q) => $q->where('vehicle_id', $vehicleId))
                    ->when($request->category_id, fn($q) => $q->where('category_id', $request->category_id))
                    ->orderBy('date', 'desc')->get()
                    ->map(fn($r) => [
                        'date'     => $r->date,
                        'category' => $r->category->name ?? '—',
                        'vehicle'  => $r->vehicle->vehicle_number ?? '—',
                        'amount'   => 'Rs. ' . number_format($r->amount),
                        'notes'    => $r->notes ?? '—',
                        '_amount_raw' => $r->amount,
                    ]);
                $columns = [
                    ['key'=>'date','label'=>'Date'],['key'=>'category','label'=>'Category'],
                    ['key'=>'vehicle','label'=>'Vehicle'],['key'=>'amount','label'=>'Amount'],
                    ['key'=>'notes','label'=>'Notes'],
                ];
                $summary = ['Total' => 'Rs. ' . number_format($rows->sum('_amount_raw'))];
                break;

            // ── 3. Vehicle-wise Expense ───────────────────────────────────
            case 'vehicle_expense':
                $raw = Expense::with('vehicle')
                    ->when($from, fn($q) => $q->whereDate('date', '>=', $from))
                    ->when($to,   fn($q) => $q->whereDate('date', '<=', $to))
                    ->when($vehicleId, fn($q) => $q->where('vehicle_id', $vehicleId))
                    ->get()
                    ->groupBy(fn($r) => $r->vehicle->vehicle_number ?? 'Unassigned');
                $rows = $raw->map(fn($group, $vnum) => [
                    'vehicle'     => $vnum,
                    'count'       => $group->count(),
                    'total'       => 'Rs. ' . number_format($group->sum('amount')),
                    '_total_raw'  => $group->sum('amount'),
                ])->values();
                $columns = [
                    ['key'=>'vehicle','label'=>'Vehicle'],['key'=>'count','label'=>'# Entries'],
                    ['key'=>'total','label'=>'Total Expense'],
                ];
                $summary = ['Grand Total' => 'Rs. ' . number_format($rows->sum('_total_raw'))];
                break;

            // ── 4. Vehicle-wise Maintenance ───────────────────────────────
            case 'vehicle_maintenance':
                $raw = Maintenance::with('vehicle')
                    ->when($from, fn($q) => $q->whereDate('maintenance_date', '>=', $from))
                    ->when($to,   fn($q) => $q->whereDate('maintenance_date', '<=', $to))
                    ->when($vehicleId, fn($q) => $q->where('vehicle_id', $vehicleId))
                    ->get()
                    ->groupBy(fn($r) => $r->vehicle->vehicle_number ?? 'Unknown');
                $rows = $raw->map(fn($group, $vnum) => [
                    'vehicle' => $vnum,
                    'count'   => $group->count(),
                    'cost'    => 'Rs. ' . number_format($group->sum('amount')),
                    '_cost_raw' => $group->sum('amount'),
                ])->values();
                $columns = [
                    ['key'=>'vehicle','label'=>'Vehicle'],['key'=>'count','label'=>'# Services'],
                    ['key'=>'cost','label'=>'Total Cost'],
                ];
                $summary = ['Grand Total' => 'Rs. ' . number_format($rows->sum('_cost_raw'))];
                break;

            // ── 5. Diesel Consumption ─────────────────────────────────────
            case 'diesel':
                $rows = FleetDailyData::with(['vehicle', 'driver'])
                    ->when($from, fn($q) => $q->whereDate('date', '>=', $from))
                    ->when($to,   fn($q) => $q->whereDate('date', '<=', $to))
                    ->when($vehicleId, fn($q) => $q->where('vehicle_id', $vehicleId))
                    ->when($driverId,  fn($q) => $q->where('driver_id', $driverId))
                    ->orderBy('date', 'desc')->get()
                    ->map(fn($r) => [
                        'date'    => $r->date,
                        'vehicle' => $r->vehicle->vehicle_number ?? '—',
                        'driver'  => $r->driver->name ?? '—',
                        'liters'  => $r->daily_diesel_liters ?? 0,
                        'amount'  => 'Rs. ' . number_format($r->daily_diesel_amount ?? 0),
                        'km'      => $r->total_km ?? 0,
                        'km_per_l'=> $r->daily_diesel_liters > 0 ? round(($r->total_km / $r->daily_diesel_liters), 2) : '—',
                        '_amount_raw' => $r->daily_diesel_amount ?? 0,
                        '_liters_raw' => $r->daily_diesel_liters ?? 0,
                    ]);
                $columns = [
                    ['key'=>'date','label'=>'Date'],['key'=>'vehicle','label'=>'Vehicle'],
                    ['key'=>'driver','label'=>'Driver'],['key'=>'liters','label'=>'Liters'],
                    ['key'=>'amount','label'=>'Diesel Cost'],['key'=>'km','label'=>'Total KM'],
                    ['key'=>'km_per_l','label'=>'KM/Liter'],
                ];
                $summary = [
                    'Total Liters' => number_format($rows->sum('_liters_raw')),
                    'Total Cost'   => 'Rs. ' . number_format($rows->sum('_amount_raw')),
                ];
                break;

            // ── 6. Daily KM ───────────────────────────────────────────────
            case 'daily_km':
                $rows = FleetDailyData::with(['vehicle', 'driver'])
                    ->when($from, fn($q) => $q->whereDate('date', '>=', $from))
                    ->when($to,   fn($q) => $q->whereDate('date', '<=', $to))
                    ->when($vehicleId, fn($q) => $q->where('vehicle_id', $vehicleId))
                    ->when($driverId,  fn($q) => $q->where('driver_id', $driverId))
                    ->orderBy('date', 'desc')->get()
                    ->map(fn($r) => [
                        'date'    => $r->date,
                        'vehicle' => $r->vehicle->vehicle_number ?? '—',
                        'driver'  => $r->driver->name ?? '—',
                        'main_km' => $r->main_km ?? 0,
                        'local_km'=> $r->local_km ?? 0,
                        'total_km'=> $r->total_km ?? 0,
                        '_km_raw' => $r->total_km ?? 0,
                    ]);
                $columns = [
                    ['key'=>'date','label'=>'Date'],['key'=>'vehicle','label'=>'Vehicle'],
                    ['key'=>'driver','label'=>'Driver'],['key'=>'main_km','label'=>'Main KM'],
                    ['key'=>'local_km','label'=>'Local KM'],['key'=>'total_km','label'=>'Total KM'],
                ];
                $summary = ['Total KM' => number_format($rows->sum('_km_raw')) . ' km'];
                break;

            // ── 7. Driver Salary ──────────────────────────────────────────
            case 'driver_salary':
                $rows = DriverSalary::with('driver')
                    ->when($from, fn($q) => $q->whereDate('salary_period', '>=', $from))
                    ->when($to,   fn($q) => $q->whereDate('salary_period', '<=', $to))
                    ->when($driverId, fn($q) => $q->where('driver_id', $driverId))
                    ->orderBy('salary_period', 'desc')->get()
                    ->map(fn($r) => [
                        'period'  => $r->salary_period,
                        'driver'  => $r->driver->name ?? '—',
                        'basic'   => 'Rs. ' . number_format($r->gross_salary ?? 0),
                        'fine'    => 'Rs. ' . number_format($r->fine ?? 0),
                        'net'     => 'Rs. ' . number_format($r->net_payable ?? 0),
                        'status'  => ucfirst($r->payment_status ?? 'pending'),
                        '_net_raw'=> $r->net_payable ?? 0,
                    ]);
                $columns = [
                    ['key'=>'period','label'=>'Period'],['key'=>'driver','label'=>'Driver'],
                    ['key'=>'basic','label'=>'Basic'],['key'=>'fine','label'=>'Fine'],
                    ['key'=>'net','label'=>'Net Salary'],['key'=>'status','label'=>'Status'],
                ];
                $summary = ['Total Net Salary' => 'Rs. ' . number_format($rows->sum('_net_raw'))];
                break;

            // ── 8. Driver Fine ────────────────────────────────────────────
            case 'driver_fine':
                $rows = DriverSalary::with('driver')
                    ->where('fine', '>', 0)
                    ->when($from, fn($q) => $q->whereDate('salary_period', '>=', $from))
                    ->when($to,   fn($q) => $q->whereDate('salary_period', '<=', $to))
                    ->when($driverId, fn($q) => $q->where('driver_id', $driverId))
                    ->orderBy('salary_period', 'desc')->get()
                    ->map(fn($r) => [
                        'period'     => $r->salary_period,
                        'driver'     => $r->driver->name ?? '—',
                        'fine'       => 'Rs. ' . number_format($r->fine ?? 0),
                        'fine_reason'=> $r->remarks ?? '—',
                        '_fine_raw'  => $r->fine ?? 0,
                    ]);
                $columns = [
                    ['key'=>'period','label'=>'Period'],['key'=>'driver','label'=>'Driver'],
                    ['key'=>'fine','label'=>'Fine Amount'],['key'=>'fine_reason','label'=>'Reason'],
                ];
                $summary = ['Total Fines' => 'Rs. ' . number_format($rows->sum('_fine_raw'))];
                break;

            // ── 9. Pasgi/Advance ──────────────────────────────────────────
            case 'pasgi_advance':
                $rows = PasgiAdvance::with('driver')
                    ->when($from, fn($q) => $q->whereDate('date', '>=', $from))
                    ->when($to,   fn($q) => $q->whereDate('date', '<=', $to))
                    ->when($driverId, fn($q) => $q->where('driver_id', $driverId))
                    ->orderBy('date', 'desc')->get()
                    ->map(function ($r) {
                        $recovered = PasgiAdjustment::where('driver_id', $r->driver_id)->sum('amount');
                        return [
                            'date'      => $r->date,
                            'driver'    => $r->driver->name ?? '—',
                            'given'     => 'Rs. ' . number_format($r->amount),
                            'recovered' => 'Rs. ' . number_format($recovered),
                            'remarks'   => $r->remarks ?? '—',
                            '_amount_raw' => $r->amount,
                        ];
                    });
                $columns = [
                    ['key'=>'date','label'=>'Date'],['key'=>'driver','label'=>'Driver'],
                    ['key'=>'given','label'=>'Amount Given'],['key'=>'recovered','label'=>'Total Recovered'],
                    ['key'=>'remarks','label'=>'Remarks'],
                ];
                $summary = ['Total Given' => 'Rs. ' . number_format($rows->sum('_amount_raw'))];
                break;

            // ── 10. Store Expense ─────────────────────────────────────────
            case 'store_expense':
                $rows = StoreItem::with('vehicle')
                    ->when($from, fn($q) => $q->whereDate('date', '>=', $from))
                    ->when($to,   fn($q) => $q->whereDate('date', '<=', $to))
                    ->when($vehicleId, fn($q) => $q->where('vehicle_id', $vehicleId))
                    ->orderBy('date', 'desc')->get()
                    ->map(fn($r) => [
                        'date'    => $r->date,
                        'item'    => $r->item_name,
                        'vehicle' => $r->vehicle->vehicle_number ?? '—',
                        'qty'     => $r->quantity,
                        'unit'    => $r->unit ?? '—',
                        'cost'    => 'Rs. ' . number_format($r->amount ?? 0),
                        '_cost_raw' => $r->amount ?? 0,
                    ]);
                $columns = [
                    ['key'=>'date','label'=>'Date'],['key'=>'item','label'=>'Item'],
                    ['key'=>'vehicle','label'=>'Vehicle'],['key'=>'qty','label'=>'Qty'],
                    ['key'=>'unit','label'=>'Unit'],['key'=>'cost','label'=>'Total Cost'],
                ];
                $summary = ['Total' => 'Rs. ' . number_format($rows->sum('_cost_raw'))];
                break;

            // ── 11. Office Expense ────────────────────────────────────────
            case 'office_expense':
                $rows = Expense::with('category')
                    ->whereHas('category', fn($q) => $q->where('name', 'like', '%office%')->orWhere('name', 'like', '%admin%')->orWhere('name', 'like', '%general%'))
                    ->when($from, fn($q) => $q->whereDate('date', '>=', $from))
                    ->when($to,   fn($q) => $q->whereDate('date', '<=', $to))
                    ->orderBy('date', 'desc')->get()
                    ->map(fn($r) => [
                        'date'     => $r->date,
                        'category' => $r->category->name ?? '—',
                        'amount'   => 'Rs. ' . number_format($r->amount),
                        'notes'    => $r->notes ?? '—',
                        '_amount_raw' => $r->amount,
                    ]);
                $columns = [
                    ['key'=>'date','label'=>'Date'],['key'=>'category','label'=>'Category'],
                    ['key'=>'amount','label'=>'Amount'],['key'=>'notes','label'=>'Notes'],
                ];
                $summary = ['Total' => 'Rs. ' . number_format($rows->sum('_amount_raw'))];
                break;

            // ── 12. Profit & Loss ─────────────────────────────────────────
            case 'profit_loss':
                $totalIncome = Income::when($from, fn($q) => $q->whereDate('date', '>=', $from))
                                     ->when($to,   fn($q) => $q->whereDate('date', '<=', $to))
                                     ->sum('amount');
                $totalExpense = Expense::when($from, fn($q) => $q->whereDate('date', '>=', $from))
                                       ->when($to,   fn($q) => $q->whereDate('date', '<=', $to))
                                       ->sum('amount');
                $totalMaint   = Maintenance::when($from, fn($q) => $q->whereDate('maintenance_date', '>=', $from))
                                           ->when($to,   fn($q) => $q->whereDate('maintenance_date', '<=', $to))
                                           ->sum('amount');
                $totalSalary  = DriverSalary::when($from, fn($q) => $q->whereDate('salary_period', '>=', $from))
                                             ->when($to,   fn($q) => $q->whereDate('salary_period', '<=', $to))
                                             ->sum('net_payable')
                               + EmployeeSalary::when($from, fn($q) => $q->whereDate('salary_period', '>=', $from))
                                               ->when($to,   fn($q) => $q->whereDate('salary_period', '<=', $to))
                                               ->sum('net_payable');
                $totalStore   = StoreItem::when($from, fn($q) => $q->whereDate('date', '>=', $from))
                                         ->when($to,   fn($q) => $q->whereDate('date', '<=', $to))
                                         ->sum('amount');
                $totalDiesel  = FleetDailyData::when($from, fn($q) => $q->whereDate('date', '>=', $from))
                                              ->when($to,   fn($q) => $q->whereDate('date', '<=', $to))
                                              ->sum('daily_diesel_amount');

                $allExpenses = $totalExpense + $totalMaint + $totalSalary + $totalStore;
                $netProfit   = $totalIncome - $allExpenses;

                $rows = collect([
                    ['category' => 'Total Income', 'amount' => 'Rs. ' . number_format($totalIncome), 'type' => 'income', '_raw' => $totalIncome],
                    ['category' => '— Expenses (General)', 'amount' => 'Rs. ' . number_format($totalExpense), 'type' => 'expense', '_raw' => -$totalExpense],
                    ['category' => '— Maintenance Cost', 'amount' => 'Rs. ' . number_format($totalMaint), 'type' => 'expense', '_raw' => -$totalMaint],
                    ['category' => '— Salaries Paid', 'amount' => 'Rs. ' . number_format($totalSalary), 'type' => 'expense', '_raw' => -$totalSalary],
                    ['category' => '— Store / Parts', 'amount' => 'Rs. ' . number_format($totalStore), 'type' => 'expense', '_raw' => -$totalStore],
                    ['category' => 'NET PROFIT / LOSS', 'amount' => 'Rs. ' . number_format($netProfit), 'type' => $netProfit >= 0 ? 'profit' : 'loss', '_raw' => $netProfit],
                ]);
                $columns = [
                    ['key'=>'category','label'=>'Category / Item'],
                    ['key'=>'amount','label'=>'Amount'],
                ];
                $summary = ['Net Result' => ($netProfit >= 0 ? 'Profit: ' : 'Loss: ') . 'Rs. ' . number_format(abs($netProfit))];
                break;

            default:
                $rows = collect();
                $columns = [];
                $summary = [];
        }

        return [$rows ?? collect(), $columns ?? [], $summary ?? []];
    }
}
