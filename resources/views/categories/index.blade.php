@extends('layouts.app')

@section('title', 'Category Management')
@section('breadcrumbs', 'Categories')
@section('page_title', 'Category Management')

@section('content')
<div class="card table-card">
    <div class="chart-header" style="display: flex; flex-direction: column; align-items: flex-start; gap: 16px; width: 100%; border-bottom: none; padding-bottom: 0;">
        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; flex-wrap: wrap; gap: 15px;">
            <div class="chart-title-block">
                <span class="chart-title">System Categories</span>
                <span class="chart-subtitle">Manage vehicle expense, income, and maintenance categories</span>
            </div>
            <a href="{{ route('categories.create') }}" class="btn-signin" style="padding: 10px 18px; width: auto; display: inline-flex; align-items: center; gap: 8px; font-size: 12px; margin: 0; white-space: nowrap;">
                <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                <span>Add Category</span>
            </a>
        </div>
        <div style="width: 100%; display: flex; justify-content: flex-start; align-items: center; background: rgba(0,0,0,0.02); padding: 12px 16px; border-radius: 8px; border: 1px solid var(--border-color); box-shadow: inset 0 1px 2px rgba(0,0,0,0.02);">
            <form action="{{ route('categories.index') }}" method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap; margin: 0; width: 100%;">
                <input type="text" name="search" class="form-input" placeholder="Search by name..." value="{{ request('search') }}" style="height: 38px; padding: 5px 15px; font-size: 13px; max-width: 200px; margin: 0;">
                <select name="type" class="form-input" style="height: 38px; padding: 5px 15px; font-size: 13px; width: auto; margin: 0;">
                    <option value="">All Types</option>
                    <option value="income" {{ request('type') === 'income' ? 'selected' : '' }}>Income</option>
                    <option value="expense" {{ request('type') === 'expense' ? 'selected' : '' }}>Expense</option>
                    <option value="maintenance" {{ request('type') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                    <option value="general" {{ request('type') === 'general' ? 'selected' : '' }}>General</option>
                </select>
                <button type="submit" class="btn-signin" style="padding: 0 20px; height: 38px; margin: 0; width: auto; font-size: 12px; background: var(--primary); border-color: var(--primary);">Filter</button>
                @if(request('search') || request('type'))
                    <a href="{{ route('categories.index') }}" class="btn-signin" style="padding: 10px 20px; height: 38px; margin: 0; width: auto; font-size: 12px; background: #718096; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; color: #fff;">Clear</a>
                @endif
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="purity-table">
            <thead>
                <tr>
                    <th>Category Name</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                <tr>
                    <td>
                        <strong style="color: var(--text-color);">{{ $category->name }}</strong>
                    </td>
                    <td>
                        <span class="badge info" style="font-size: 10px; text-transform: uppercase;">
                            {{ $category->type }}
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ $category->is_active ? 'active' : 'danger' }}" style="font-size: 10px;">
                            {{ $category->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td style="text-align: right;">
                        <div style="display: inline-flex; gap: 15px; align-items: center;">
                            <a href="{{ route('categories.edit', $category->id) }}" style="color: var(--primary); text-decoration: none; font-weight: 700; font-size: 12px;">Edit</a>
                            
                            <form action="{{ route('categories.destroy', $category->id) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="delete-btn" style="background: none; border: none; color: #E53E3E; cursor: pointer; font-weight: 700; font-size: 12px; padding: 0;">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 30px;">No categories found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div style="margin-top: 15px;">
        {{ $categories->links() }}
    </div>
</div>
@endsection
