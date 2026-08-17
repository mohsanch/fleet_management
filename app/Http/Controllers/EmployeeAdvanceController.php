<?php

namespace App\Http\Controllers;

use App\Models\EmployeeAdvance;
use App\Models\Employee;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class EmployeeAdvanceController extends Controller implements HasMiddleware
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
        $query = EmployeeAdvance::with(['employee', 'creator']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('remarks', 'like', '%' . $search . '%')
                  ->orWhereHas('employee', function($eq) use ($search) {
                      $eq->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        $advances = $query->orderBy('date', 'desc')->paginate(10);
        return view('employee_advances.index', compact('advances'));
    }

    public function create()
    {
        $employees = Employee::where('status', 'active')->orderBy('name')->get();
        return view('employee_advances.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'amount'      => ['required', 'numeric', 'min:1'],
            'date'        => ['required', 'date'],
            'remarks'     => ['nullable', 'string'],
        ]);

        $data = $request->all();
        $data['created_by'] = auth()->id();

        $advance = EmployeeAdvance::create($data);

        ActivityLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'CREATE',
            'model_type'  => 'EmployeeAdvance',
            'model_id'    => $advance->id,
            'description' => "Issued Employee Advance Rs. " . number_format($advance->amount) . " to: " . $advance->employee->name,
        ]);

        return redirect()->route('employee-advances.index')->with('success', 'Employee advance issued successfully.');
    }

    public function edit(EmployeeAdvance $employeeAdvance)
    {
        $employees = Employee::where('status', 'active')->orWhere('id', $employeeAdvance->employee_id)->orderBy('name')->get();
        return view('employee_advances.edit', compact('employeeAdvance', 'employees'));
    }

    public function update(Request $request, EmployeeAdvance $employeeAdvance)
    {
        $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'amount'      => ['required', 'numeric', 'min:1'],
            'date'        => ['required', 'date'],
            'remarks'     => ['nullable', 'string'],
        ]);

        $employeeAdvance->update($request->all());

        ActivityLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'UPDATE',
            'model_type'  => 'EmployeeAdvance',
            'model_id'    => $employeeAdvance->id,
            'description' => "Updated Employee Advance #{$employeeAdvance->id} for: " . $employeeAdvance->employee->name,
        ]);

        return redirect()->route('employee-advances.index')->with('success', 'Employee advance updated successfully.');
    }

    public function destroy(EmployeeAdvance $employeeAdvance)
    {
        $name   = $employeeAdvance->employee->name;
        $amount = $employeeAdvance->amount;
        $employeeAdvance->delete();

        ActivityLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'DELETE',
            'model_type'  => 'EmployeeAdvance',
            'model_id'    => $employeeAdvance->id,
            'description' => "Deleted Employee Advance for {$name}: Rs. " . number_format($amount),
        ]);

        return redirect()->route('employee-advances.index')->with('success', 'Employee advance deleted.');
    }
}
