<?php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\Category;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class IncomeController extends Controller implements HasMiddleware
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
$query = Income::with(['category', 'creator']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('reference_source', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhereHas('category', function($cq) use ($search) {
                      $cq->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        $query->when($request->filled('date_from'), fn($q) => $q->whereDate('date', '>=', $request->date_from))
              ->when($request->filled('date_to'), fn($q) => $q->whereDate('date', '<=', $request->date_to))
              ->when($request->filled('category_id'), fn($q) => $q->where('category_id', $request->category_id));

        $incomes = $query->orderBy('date', 'desc')->paginate(10)->withQueryString();
        $categories = Category::where('type', 'income')->where('is_active', true)->orderBy('name')->get();

        return view('incomes.index', compact('incomes', 'categories'));
    }

    public function create()
    {
        $categories = Category::where('type', 'income')->where('is_active', true)->orderBy('name')->get();
        return view('incomes.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'date' => ['required', 'date'],
            'reference_source' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $data = $request->all();
        $data['created_by'] = auth()->id();

        $income = Income::create($data);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'CREATE',
            'model_type' => 'Income',
            'model_id' => $income->id,
            'description' => "Logged Income: Rs. " . number_format($income->amount) . " (Category: " . $income->category->name . ")",
        ]);

        return redirect()->route('incomes.index')->with('success', 'Income record added successfully.');
    }

    public function edit(Income $income)
    {
        $categories = Category::where('type', 'income')->where('is_active', true)->orderBy('name')->get();
        return view('incomes.edit', compact('income', 'categories'));
    }

    public function update(Request $request, Income $income)
    {
        $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'date' => ['required', 'date'],
            'reference_source' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'edit_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $income->update($request->except('edit_reason'));

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'UPDATE',
            'model_type' => 'Income',
            'model_id' => $income->id,
            'description' => "Updated Income record #{$income->id}: Rs. " . number_format($income->amount),
            'reason' => $request->edit_reason,
        ]);

        return redirect()->route('incomes.index')->with('success', 'Income record updated successfully.');
    }

    public function destroy(Income $income)
    {
        $amount = $income->amount;
        $category = $income->category->name;
        $income->delete();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'DELETE',
            'model_type' => 'Income',
            'model_id' => $income->id,
            'description' => "Deleted Income record: Rs. " . number_format($amount) . " (Category: {$category})",
        ]);

        return redirect()->route('incomes.index')->with('success', 'Income record deleted successfully.');
    }
}
