<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        $query = Branch::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
        }

        $branches = $query->orderBy('name')->paginate(10);
        return view('admin.branches.index', compact('branches'));
    }

    public function create()
    {
        return view('admin.branches.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:10', 'unique:branches,code'],
            'is_active' => ['required', 'boolean'],
        ]);

        $branch = Branch::create($request->all());

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'CREATE',
            'model_type' => 'Branch',
            'model_id' => $branch->id,
            'description' => "Created Branch: {$branch->name} ({$branch->code})",
        ]);

        return redirect()->route('admin.branches.index')->with('success', 'Branch created successfully.');
    }

    public function edit(Branch $branch)
    {
        return view('admin.branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:10', 'unique:branches,code,' . $branch->id],
            'is_active' => ['required', 'boolean'],
        ]);

        $branch->update($request->all());

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'UPDATE',
            'model_type' => 'Branch',
            'model_id' => $branch->id,
            'description' => "Updated Branch: {$branch->name} ({$branch->code})",
        ]);

        return redirect()->route('admin.branches.index')->with('success', 'Branch updated successfully.');
    }

    public function destroy(Branch $branch)
    {
        // Check if there are users, vehicles, drivers, or employees assigned to this branch
        if ($branch->users()->count() > 0 || $branch->vehicles()->count() > 0 || $branch->drivers()->count() > 0 || $branch->employees()->count() > 0) {
            return redirect()->route('admin.branches.index')->with('error', 'Cannot delete branch because it has associated users, vehicles, drivers, or employees.');
        }

        $name = $branch->name;
        $branch->delete();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'DELETE',
            'model_type' => 'Branch',
            'model_id' => $branch->id,
            'description' => "Deleted Branch: {$name}",
        ]);

        return redirect()->route('admin.branches.index')->with('success', 'Branch deleted successfully.');
    }

    public function switchBranch(Request $request)
    {
        $request->validate([
            'branch_id' => ['nullable', 'string'],
        ]);

        // Only allow admins/super admins with no branch constraint to switch
        if (auth()->user()->branch_id !== null || !in_array(auth()->user()->user_type, ['super_admin', 'admin'])) {
            return redirect()->back()->with('error', 'You do not have permission to switch branches.');
        }

        if ($request->branch_id === 'all' || empty($request->branch_id)) {
            session()->forget('active_branch_id');
        } else {
            $branch = Branch::findOrFail($request->branch_id);
            session(['active_branch_id' => $branch->id]);
        }

        return redirect()->back()->with('success', 'Switched to selected branch view.');
    }
}
