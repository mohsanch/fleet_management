<?php

namespace App\Http\Controllers;

use App\Models\EmployeeSalary;
use App\Models\Employee;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class EmployeeSalaryController extends Controller implements HasMiddleware
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
        $query = EmployeeSalary::with(['employee', 'creator']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('salary_period', 'like', '%' . $search . '%')
                  ->orWhere('payment_status', 'like', '%' . $search . '%')
                  ->orWhereHas('employee', function($eq) use ($search) {
                      $eq->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        if ($request->filled('period')) {
            $query->where('salary_period', $request->period);
        }

        $salaries = $query->orderBy('salary_period', 'desc')->paginate(10);
        return view('employee_salaries.index', compact('salaries'));
    }

    public function create()
    {
        $employees = Employee::where('status', 'active')->orderBy('name')->get();
        return view('employee_salaries.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id'      => ['required', 'exists:employees,id'],
            'salary_period'    => ['required', 'string'],
            'gross_salary'     => ['required', 'numeric', 'min:0'],
            'fine'             => ['nullable', 'numeric', 'min:0'],
            'advance_adjustment' => ['nullable', 'numeric', 'min:0'],
            'other_adjustment' => ['nullable', 'numeric'],
            'payment_date'     => ['required', 'date'],
            'payment_status'   => ['required', 'in:paid,pending'],
            'remarks'          => ['nullable', 'string'],
        ]);

        $data = $request->all();
        $data['created_by'] = auth()->id();

        $salary = EmployeeSalary::create($data);

        ActivityLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'CREATE',
            'model_type'  => 'EmployeeSalary',
            'model_id'    => $salary->id,
            'description' => "Paid Employee Salary: " . $salary->employee->name . " for period " . $salary->salary_period . " — Rs. " . number_format($salary->net_payable),
        ]);

        return redirect()->route('employee-salaries.index')->with('success', 'Employee salary processed successfully.');
    }

    public function edit(EmployeeSalary $employeeSalary)
    {
        $employees = Employee::where('status', 'active')->orWhere('id', $employeeSalary->employee_id)->orderBy('name')->get();
        return view('employee_salaries.edit', compact('employeeSalary', 'employees'));
    }

    public function update(Request $request, EmployeeSalary $employeeSalary)
    {
        $request->validate([
            'employee_id'      => ['required', 'exists:employees,id'],
            'salary_period'    => ['required', 'string'],
            'gross_salary'     => ['required', 'numeric', 'min:0'],
            'fine'             => ['nullable', 'numeric', 'min:0'],
            'advance_adjustment' => ['nullable', 'numeric', 'min:0'],
            'other_adjustment' => ['nullable', 'numeric'],
            'payment_date'     => ['required', 'date'],
            'payment_status'   => ['required', 'in:paid,pending'],
            'remarks'          => ['nullable', 'string'],
        ]);

        $employeeSalary->update($request->all());

        ActivityLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'UPDATE',
            'model_type'  => 'EmployeeSalary',
            'model_id'    => $employeeSalary->id,
            'description' => "Updated Employee Salary #{$employeeSalary->id}: " . $employeeSalary->employee->name . " Rs. " . number_format($employeeSalary->net_payable),
        ]);

        return redirect()->route('employee-salaries.index')->with('success', 'Employee salary updated successfully.');
    }

    public function destroy(EmployeeSalary $employeeSalary)
    {
        $name   = $employeeSalary->employee->name;
        $period = $employeeSalary->salary_period;
        $employeeSalary->delete();

        ActivityLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'DELETE',
            'model_type'  => 'EmployeeSalary',
            'model_id'    => $employeeSalary->id,
            'description' => "Deleted Employee Salary for {$name} ({$period})",
        ]);

        return redirect()->route('employee-salaries.index')->with('success', 'Salary record deleted.');
    }
}
