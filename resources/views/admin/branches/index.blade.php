@extends('layouts.app')

@section('title', 'Branch Management')
@section('breadcrumbs', 'Admin / Branches')
@section('page_title', 'Branch Management')

@section('content')

{{-- ── STATS ── --}}
<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:18px;margin-bottom:28px;">
    <div class="card" style="padding:18px 20px;display:flex;align-items:center;gap:14px;">
        <div style="width:40px;height:40px;border-radius:10px;background:rgba(99,102,241,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i data-lucide="map-pin" style="width:18px;height:18px;color:var(--primary);"></i>
        </div>
        <div>
            <div style="font-size:22px;font-weight:800;color:var(--text-color);line-height:1;">{{ $branches->count() }}</div>
            <div style="font-size:10px;color:var(--text-muted);margin-top:2px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Total Branches</div>
        </div>
    </div>
    <div class="card" style="padding:18px 20px;display:flex;align-items:center;gap:14px;">
        <div style="width:40px;height:40px;border-radius:10px;background:rgba(56,161,105,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i data-lucide="check-circle" style="width:18px;height:18px;color:#38A169;"></i>
        </div>
        <div>
            <div style="font-size:22px;font-weight:800;color:var(--text-color);line-height:1;">{{ $branches->where('is_active', true)->count() }}</div>
            <div style="font-size:10px;color:var(--text-muted);margin-top:2px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Active Branches</div>
        </div>
    </div>
</div>

{{-- ── BRANCHES TABLE ── --}}
<div class="card table-card">
    <div class="chart-header" style="margin-bottom:20px;align-items:center;">
        <div class="chart-title-block">
            <span class="chart-title">All Branches</span>
            <span class="chart-subtitle">Add, edit, enable, disable or remove company branches</span>
        </div>
        <a href="{{ route('admin.branches.create') }}" class="btn-signin" style="margin:0;width:auto;padding:10px 20px;font-size:13px;text-decoration:none;display:inline-flex;align-items:center;gap:6px;white-space:nowrap;">
            <i data-lucide="plus" style="width:14px;height:14px;"></i> New Branch
        </a>
    </div>

    <div class="table-responsive">
        <table class="purity-table">
            <thead>
                <tr>
                    <th>Branch Name</th>
                    <th>Code</th>
                    <th>Status</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($branches as $branch)
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:34px;height:34px;border-radius:10px;background:rgba(99,102,241,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i data-lucide="map-pin" style="width:15px;height:15px;color:var(--primary);"></i>
                            </div>
                            <div>
                                <strong style="font-size:13px;color:var(--text-color);">{{ $branch->name }}</strong>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span style="font-size:13px;font-weight:700;color:var(--text-color);">{{ $branch->code }}</span>
                    </td>
                    <td>
                        <span style="display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:700;background:{{ $branch->is_active ? 'rgba(56,161,105,0.12)' : 'rgba(229,62,62,0.12)' }};color:{{ $branch->is_active ? '#38A169' : '#E53E3E' }};padding:3px 10px;border-radius:20px;">
                            {{ $branch->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td style="text-align:right;">
                        <div style="display: inline-flex; gap: 15px; align-items: center; justify-content: flex-end; width: 100%;">
                            <a href="{{ route('admin.branches.edit', $branch->id) }}" style="color: var(--primary); text-decoration: none; font-weight: 700; font-size: 12px;">Edit</a>
                            
                            <form action="{{ route('admin.branches.destroy', $branch->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this branch?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="delete-btn" style="background: none; border: none; color: #E53E3E; cursor: pointer; font-weight: 700; font-size: 12px; padding: 0;">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 30px;">No branches found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div style="margin-top: 15px;">
        {{ $branches->links() }}
    </div>
</div>

@endsection
