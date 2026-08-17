<?php

namespace App\Http\Controllers;

use App\Models\Maintenance;
use App\Models\Vehicle;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class MaintenanceController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:add-transactions', only: ['create', 'store']),
            new Middleware('can:edit-transactions', only: ['edit', 'update']),
            new Middleware('can:delete-transactions', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
$query = Maintenance::with(['vehicle', 'creator']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('maintenance_type', 'like', '%' . $search . '%')
                  ->orWhere('vendor', 'like', '%' . $search . '%')
                  ->orWhere('invoice_number', 'like', '%' . $search . '%')
                  ->orWhereHas('vehicle', function($vq) use ($search) {
                      $vq->where('vehicle_number', 'like', '%' . $search . '%');
                  });
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('maintenance_date', $request->date);
        }

        $query->when($request->filled('date_from'), fn($q) => $q->whereDate('maintenance_date', '>=', $request->date_from))
              ->when($request->filled('date_to'), fn($q) => $q->whereDate('maintenance_date', '<=', $request->date_to))
              ->when($request->filled('vehicle_id'), fn($q) => $q->where('vehicle_id', $request->vehicle_id));

        $maintenances = $query->orderBy('maintenance_date', 'desc')->paginate(10)->withQueryString();
        $vehicles = Vehicle::orderBy('vehicle_number')->get();

        return view('maintenances.index', compact('maintenances', 'vehicles'));
    }

    public function create()
    {
        $vehicles = Vehicle::where('status', 'active')->orderBy('vehicle_number')->get();
        return view('maintenances.create', compact('vehicles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'maintenance_date' => ['required', 'date'],
            'maintenance_type' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'vendor' => ['required', 'string', 'max:255'],
            'invoice_number' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
        ]);

        $data = $request->all();
        $data['created_by'] = auth()->id();

        $maintenance = Maintenance::create($data);

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('attachments', 'public');
            $maintenance->attachments()->create([
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'created_by' => auth()->id(),
            ]);
        }

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'CREATE',
            'model_type' => 'Maintenance',
            'model_id' => $maintenance->id,
            'description' => "Logged Maintenance: " . $maintenance->maintenance_type . " for Vehicle: " . $maintenance->vehicle->vehicle_number,
        ]);

        return redirect()->route('maintenances.index')->with('success', 'Maintenance record added successfully.');
    }

    public function edit(Maintenance $maintenance)
    {
        $maintenance->load('attachments');
        $vehicles = Vehicle::where('status', 'active')->orWhere('id', $maintenance->vehicle_id)->orderBy('vehicle_number')->get();
        return view('maintenances.edit', compact('maintenance', 'vehicles'));
    }

    public function update(Request $request, Maintenance $maintenance)
    {
        $request->validate([
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'maintenance_date' => ['required', 'date'],
            'maintenance_type' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'vendor' => ['required', 'string', 'max:255'],
            'invoice_number' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
        ]);

        $maintenance->update($request->all());

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('attachments', 'public');
            $maintenance->attachments()->create([
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'created_by' => auth()->id(),
            ]);
        }

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'UPDATE',
            'model_type' => 'Maintenance',
            'model_id' => $maintenance->id,
            'description' => "Updated Maintenance record #{$maintenance->id} for Vehicle: " . $maintenance->vehicle->vehicle_number,
        ]);

        return redirect()->route('maintenances.index')->with('success', 'Maintenance record updated successfully.');
    }

    public function destroy(Maintenance $maintenance)
    {
        $type = $maintenance->maintenance_type;
        $vehicle = $maintenance->vehicle->vehicle_number;
        $maintenance->delete();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'DELETE',
            'model_type' => 'Maintenance',
            'model_id' => $maintenance->id,
            'description' => "Deleted Maintenance: {$type} for Vehicle: {$vehicle}",
        ]);

        return redirect()->route('maintenances.index')->with('success', 'Maintenance record deleted successfully.');
    }
}
