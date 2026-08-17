<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function index(Request $request)
    {
        $drivers = Driver::when($request->search, function($q) use ($request) {
                return $q->where(fn($sub) => $sub->where('name', 'like', "%{$request->search}%")
                    ->orWhere('license_number', 'like', "%{$request->search}%")
                    ->orWhere('phone', 'like', "%{$request->search}%"));
            })
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('drivers.index', compact('drivers'));
    }

    public function create()
    {
        return view('drivers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'license_number' => ['required', 'string', 'max:255', 'unique:drivers'],
            'phone' => ['required', 'string', 'max:255'],
            'base_salary' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'in:active,inactive,suspended'],
        ]);

        $driver = Driver::create([
            'name' => $request->name,
            'license_number' => $request->license_number,
            'phone' => $request->phone,
            'base_salary' => $request->base_salary,
            'status' => $request->status,
        ]);

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('attachments', 'public');
            $driver->attachments()->create([
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'created_by' => auth()->id(),
            ]);
        }

        return redirect()->route('drivers.index')->with('success', 'Driver created successfully.');
    }

    public function edit(Driver $driver)
    {
        $driver->load('attachments');
        return view('drivers.edit', compact('driver'));
    }

    public function update(Request $request, Driver $driver)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'license_number' => ['required', 'string', 'max:255', 'unique:drivers,license_number,' . $driver->id],
            'phone' => ['required', 'string', 'max:255'],
            'base_salary' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string', 'in:active,inactive,suspended'],
        ]);

        $driver->update([
            'name' => $request->name,
            'license_number' => $request->license_number,
            'phone' => $request->phone,
            'base_salary' => $request->base_salary,
            'status' => $request->status,
        ]);

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('attachments', 'public');
            $driver->attachments()->create([
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'created_by' => auth()->id(),
            ]);
        }

        return redirect()->route('drivers.index')->with('success', 'Driver updated successfully.');
    }

    public function destroy(Driver $driver)
    {
        $driver->delete();
        return redirect()->route('drivers.index')->with('success', 'Driver deleted successfully.');
    }
}
