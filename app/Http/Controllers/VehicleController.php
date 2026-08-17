<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $query = Vehicle::with('driver');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('vehicle_number', 'like', '%' . $search . '%')
                  ->orWhere('registration_name', 'like', '%' . $search . '%')
                  ->orWhere('type', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $vehicles = $query->orderBy('vehicle_number')->paginate(10);
        return view('vehicles.index', compact('vehicles'));
    }

    public function create()
    {
        $drivers = Driver::where('status', 'active')->orderBy('name')->get();
        return view('vehicles.create', compact('drivers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vehicle_number' => ['required', 'string', 'max:255', 'unique:vehicles'],
            'registration_name' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],
            'assigned_driver_id' => ['nullable', 'exists:drivers,id'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ]);

        $vehicle = Vehicle::create($request->all());

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('attachments', 'public');
            $vehicle->attachments()->create([
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'created_by' => auth()->id(),
            ]);
        }

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'CREATE',
            'model_type' => 'Vehicle',
            'model_id' => $vehicle->id,
            'description' => "Registered Vehicle: {$vehicle->vehicle_number}",
        ]);

        return redirect()->route('vehicles.index')->with('success', 'Vehicle registered successfully.');
    }

    public function edit(Vehicle $vehicle)
    {
        $vehicle->load('attachments');
        $drivers = Driver::where('status', 'active')->orWhere('id', $vehicle->assigned_driver_id)->orderBy('name')->get();
        return view('vehicles.edit', compact('vehicle', 'drivers'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $request->validate([
            'vehicle_number' => ['required', 'string', 'max:255', 'unique:vehicles,vehicle_number,' . $vehicle->id],
            'registration_name' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],
            'assigned_driver_id' => ['nullable', 'exists:drivers,id'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ]);

        $vehicle->update($request->all());

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('attachments', 'public');
            $vehicle->attachments()->create([
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'created_by' => auth()->id(),
            ]);
        }

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'UPDATE',
            'model_type' => 'Vehicle',
            'model_id' => $vehicle->id,
            'description' => "Updated Vehicle: {$vehicle->vehicle_number}",
        ]);

        return redirect()->route('vehicles.index')->with('success', 'Vehicle updated successfully.');
    }

    public function destroy(Vehicle $vehicle)
    {
        $number = $vehicle->vehicle_number;
        $vehicle->delete();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'DELETE',
            'model_type' => 'Vehicle',
            'model_id' => $vehicle->id,
            'description' => "Deregistered Vehicle: {$number}",
        ]);

        return redirect()->route('vehicles.index')->with('success', 'Vehicle deleted successfully.');
    }
}
