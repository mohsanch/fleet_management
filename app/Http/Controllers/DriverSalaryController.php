<?php

namespace App\Http\Controllers;

use App\Models\DriverSalary;
use App\Models\Driver;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class DriverSalaryController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view-financials', only: ['index', 'show']),
            new Middleware('can:add-transactions', only: ['create', 'store']),
            new Middleware('can:edit-transactions', only: ['edit', 'update']),
            new Middleware('can:delete-transactions', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
$query = DriverSalary::with(['driver', 'creator']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('salary_period', 'like', '%' . $search . '%')
                  ->orWhere('payment_status', 'like', '%' . $search . '%')
                  ->orWhereHas('driver', function($dq) use ($search) {
                      $dq->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        if ($request->filled('period')) {
            $query->where('salary_period', $request->period);
        }

        $query->when($request->filled('date_from'), fn($q) => $q->whereDate('payment_date', '>=', $request->date_from))
              ->when($request->filled('date_to'), fn($q) => $q->whereDate('payment_date', '<=', $request->date_to))
              ->when($request->filled('driver_id'), fn($q) => $q->where('driver_id', $request->driver_id));

        $salaries = $query->orderBy('salary_period', 'desc')->paginate(10)->withQueryString();
        $drivers = Driver::orderBy('name')->get();

        return view('driver_salaries.index', compact('salaries', 'drivers'));
    }

    public function create()
    {
        $drivers = Driver::where('status', 'active')->orderBy('name')->get();
        return view('driver_salaries.create', compact('drivers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'driver_id'    => ['required', 'exists:drivers,id'],
            'salary_period'=> ['required', 'string'],
            'gross_salary' => ['required', 'numeric', 'min:0'],
            'fine'         => ['nullable', 'numeric', 'min:0'],
            'pasgi_adjustment' => ['nullable', 'numeric', 'min:0'],
            'other_adjustment' => ['nullable', 'numeric'],
            'payment_date' => ['required', 'date'],
            'payment_status' => ['required', 'in:paid,pending'],
            'remarks'      => ['nullable', 'string'],
        ]);

        $data = $request->all();
        $data['created_by'] = auth()->id();

        $salary = DriverSalary::create($data);

        ActivityLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'CREATE',
            'model_type'  => 'DriverSalary',
            'model_id'    => $salary->id,
            'description' => "Paid Driver Salary: " . $salary->driver->name . " for period " . $salary->salary_period . " — Rs. " . number_format($salary->net_payable),
        ]);

        return redirect()->route('driver-salaries.index')->with('success', 'Driver salary processed successfully.');
    }

    public function edit(DriverSalary $driverSalary)
    {
        $drivers = Driver::where('status', 'active')->orWhere('id', $driverSalary->driver_id)->orderBy('name')->get();
        return view('driver_salaries.edit', compact('driverSalary', 'drivers'));
    }

    public function update(Request $request, DriverSalary $driverSalary)
    {
        $request->validate([
            'driver_id'    => ['required', 'exists:drivers,id'],
            'salary_period'=> ['required', 'string'],
            'gross_salary' => ['required', 'numeric', 'min:0'],
            'fine'         => ['nullable', 'numeric', 'min:0'],
            'pasgi_adjustment' => ['nullable', 'numeric', 'min:0'],
            'other_adjustment' => ['nullable', 'numeric'],
            'payment_date' => ['required', 'date'],
            'payment_status' => ['required', 'in:paid,pending'],
            'remarks'      => ['nullable', 'string'],
        ]);

        $driverSalary->update($request->all());

        ActivityLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'UPDATE',
            'model_type'  => 'DriverSalary',
            'model_id'    => $driverSalary->id,
            'description' => "Updated Driver Salary #{$driverSalary->id}: " . $driverSalary->driver->name . " Rs. " . number_format($driverSalary->net_payable),
        ]);

        return redirect()->route('driver-salaries.index')->with('success', 'Driver salary updated successfully.');
    }

    public function destroy(DriverSalary $driverSalary)
    {
        $name = $driverSalary->driver->name;
        $period = $driverSalary->salary_period;
        $driverSalary->delete();

        ActivityLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'DELETE',
            'model_type'  => 'DriverSalary',
            'model_id'    => $driverSalary->id,
            'description' => "Deleted Driver Salary for {$name} ({$period})",
        ]);

        return redirect()->route('driver-salaries.index')->with('success', 'Salary record deleted.');
    }
}
