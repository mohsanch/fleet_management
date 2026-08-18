<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $employees = Employee::when($request->search, function($q) use ($request) {
                return $q->where(fn($sub) => $sub->where('name', 'like', "%{$request->search}%")
                    ->orWhere('designation', 'like', "%{$request->search}%")
                    ->orWhere('contact', 'like', "%{$request->search}%"));
            })
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('employees.index', compact('employees'));
    }

    public function create()
    {
        return view('employees.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'designation' => ['required', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:255'],
            'base_salary' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'branch_id' => ['nullable', 'exists:branches,id'],
        ]);

        Employee::create([
            'name' => $request->name,
            'designation' => $request->designation,
            'contact' => $request->contact,
            'base_salary' => $request->base_salary,
            'status' => $request->status,
            'branch_id' => $request->branch_id,
        ]);

        return redirect()->route('employees.index')->with('success', 'Employee created successfully.');
    }

    public function edit(Employee $employee)
    {
        return view('employees.edit', compact('employee'));
    }

    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'designation' => ['required', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:255'],
            'base_salary' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'branch_id' => ['nullable', 'exists:branches,id'],
        ]);

        $employee->update([
            'name' => $request->name,
            'designation' => $request->designation,
            'contact' => $request->contact,
            'base_salary' => $request->base_salary,
            'status' => $request->status,
            'branch_id' => $request->branch_id,
        ]);

        return redirect()->route('employees.index')->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();
        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully.');
    }
}
