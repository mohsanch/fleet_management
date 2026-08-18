<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Category;
use App\Models\Vehicle;
use App\Models\Employee;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ExpenseController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:expenses.view', only: ['index', 'show']),
            new Middleware('can:expenses.create', only: ['create', 'store']),
            new Middleware('can:expenses.edit', only: ['edit', 'update']),
            new Middleware('can:expenses.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
$query = Expense::with(['category', 'vehicle', 'employee', 'creator']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', '%' . $search . '%')
                  ->orWhere('employee_name', 'like', '%' . $search . '%')
                  ->orWhereHas('category', function($cq) use ($search) {
                      $cq->where('name', 'like', '%' . $search . '%');
                  })->orWhereHas('vehicle', function($vq) use ($search) {
                      $vq->where('vehicle_number', 'like', '%' . $search . '%');
                  });
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        $query->when($request->filled('date_from'), fn($q) => $q->whereDate('date', '>=', $request->date_from))
              ->when($request->filled('date_to'), fn($q) => $q->whereDate('date', '<=', $request->date_to))
              ->when($request->filled('category_id'), fn($q) => $q->where('category_id', $request->category_id))
              ->when($request->filled('vehicle_id'), fn($q) => $q->where('vehicle_id', $request->vehicle_id));

        $expenses = $query->orderBy('date', 'desc')->paginate(10)->withQueryString();
        $categories = Category::where('type', 'expense')->where('is_active', true)->orderBy('name')->get();
        $vehicles = Vehicle::where('status', 'active')->orderBy('vehicle_number')->get();

        return view('expenses.index', compact('expenses', 'categories', 'vehicles'));
    }

    public function create()
    {
        $categories = Category::where('type', 'expense')->where('is_active', true)->orderBy('name')->get();
        $vehicles = Vehicle::where('status', 'active')->orderBy('vehicle_number')->get();
        $employees = Employee::where('status', 'active')->orderBy('name')->get();
        return view('expenses.create', compact('categories', 'vehicles', 'employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'date' => ['required', 'date'],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'employee_id' => ['nullable', 'exists:employees,id'],
            'employee_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $data = $request->all();
        $data['created_by'] = auth()->id();

        // If employee_id is set, sync the name
        if ($request->filled('employee_id')) {
            $data['employee_name'] = Employee::where('id', $request->employee_id)->value('name');
        }

        $expense = Expense::create($data);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'CREATE',
            'model_type' => 'Expense',
            'model_id' => $expense->id,
            'description' => "Logged Expense: Rs. " . number_format($expense->amount) . " (Category: " . $expense->category->name . ")",
        ]);

        return redirect()->route('expenses.index')->with('success', 'Expense record added successfully.');
    }

    public function edit(Expense $expense)
    {
        $categories = Category::where('type', 'expense')->where('is_active', true)->orderBy('name')->get();
        $vehicles = Vehicle::where('status', 'active')->orWhere('id', $expense->vehicle_id)->orderBy('vehicle_number')->get();
        $employees = Employee::where('status', 'active')->orWhere('id', $expense->employee_id)->orderBy('name')->get();
        return view('expenses.edit', compact('expense', 'categories', 'vehicles', 'employees'));
    }

    public function update(Request $request, Expense $expense)
    {
        $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'date' => ['required', 'date'],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'employee_id' => ['nullable', 'exists:employees,id'],
            'employee_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $data = $request->all();
        if ($request->filled('employee_id')) {
            $data['employee_name'] = Employee::where('id', $request->employee_id)->value('name');
        }

        $expense->update($data);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'UPDATE',
            'model_type' => 'Expense',
            'model_id' => $expense->id,
            'description' => "Updated Expense record #{$expense->id}: Rs. " . number_format($expense->amount),
        ]);

        return redirect()->route('expenses.index')->with('success', 'Expense record updated successfully.');
    }

    public function destroy(Expense $expense)
    {
        $amount = $expense->amount;
        $category = $expense->category->name;
        $expense->delete();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'DELETE',
            'model_type' => 'Expense',
            'model_id' => $expense->id,
            'description' => "Deleted Expense record: Rs. " . number_format($amount) . " (Category: {$category})",
        ]);

        return redirect()->route('expenses.index')->with('success', 'Expense record deleted successfully.');
    }
}
