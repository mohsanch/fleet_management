<?php

namespace App\Http\Controllers;

use App\Models\FleetDailyData;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class FleetDailyDataController extends Controller implements HasMiddleware
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
$query = FleetDailyData::with(['vehicle', 'driver']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('vehicle', function($vq) use ($search) {
                    $vq->where('vehicle_number', 'like', '%' . $search . '%');
                })->orWhereHas('driver', function($dq) use ($search) {
                    $dq->where('name', 'like', '%' . $search . '%');
                });
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        $query->when($request->filled('date_from'), fn($q) => $q->whereDate('date', '>=', $request->date_from))
              ->when($request->filled('date_to'), fn($q) => $q->whereDate('date', '<=', $request->date_to))
              ->when($request->filled('vehicle_id'), fn($q) => $q->where('vehicle_id', $request->vehicle_id))
              ->when($request->filled('driver_id'), fn($q) => $q->where('driver_id', $request->driver_id));

        $dailyData = $query->orderBy('date', 'desc')->paginate(10)->withQueryString();
        $vehicles = Vehicle::orderBy('vehicle_number')->get();
        $drivers = Driver::orderBy('name')->get();

        return view('daily_data.index', compact('dailyData', 'vehicles', 'drivers'));
    }

    public function create()
    {
        $vehicles = Vehicle::where('status', 'active')->orderBy('vehicle_number')->get();
        $drivers = Driver::where('status', 'active')->orderBy('name')->get();
        return view('daily_data.create', compact('vehicles', 'drivers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => ['required', 'date'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'driver_id' => ['required', 'exists:drivers,id'],
            'pasgi_given' => ['required', 'numeric', 'min:0'],
            'daily_diesel_amount' => ['required', 'numeric', 'min:0'],
            'daily_diesel_liters' => ['required', 'numeric', 'min:0'],
            'main_km' => ['required', 'integer', 'min:0'],
            'local_km' => ['required', 'integer', 'min:0'],
            'remarks' => ['nullable', 'string'],
        ]);

        $data = $request->all();
        $data['created_by'] = auth()->id();

        $dailyLog = FleetDailyData::create($data);

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('attachments', 'public');
            $dailyLog->attachments()->create([
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'created_by' => auth()->id(),
            ]);
        }

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'CREATE',
            'model_type' => 'FleetDailyData',
            'model_id' => $dailyLog->id,
            'description' => "Logged Daily Data for Vehicle: " . $dailyLog->vehicle->vehicle_number . " on " . $dailyLog->date,
        ]);

        return redirect()->route('daily-data.index')->with('success', 'Daily operational log recorded successfully.');
    }

    public function edit(FleetDailyData $dailyLog)
    {
        // Parameter check on older records (Global Setting lock_cutoff_days)
        $cutoffDays = (int) \App\Models\Setting::where('key', 'lock_cutoff_days')->value('value') ?: 3;
        $logDate = \Carbon\Carbon::parse($dailyLog->date);
        if ($logDate->diffInDays(now()) > $cutoffDays && !auth()->user()->hasRole('Super Admin')) {
            return redirect()->route('daily-data.index')->with('error', "This log is older than {$cutoffDays} days and is locked for editing.");
        }

        $dailyLog->load('attachments');
        $vehicles = Vehicle::where('status', 'active')->orWhere('id', $dailyLog->vehicle_id)->orderBy('vehicle_number')->get();
        $drivers = Driver::where('status', 'active')->orWhere('id', $dailyLog->driver_id)->orderBy('name')->get();
        return view('daily_data.edit', compact('dailyLog', 'vehicles', 'drivers'));
    }

    public function update(Request $request, FleetDailyData $dailyLog)
    {
        // Parameter check on older records (Global Setting lock_cutoff_days)
        $cutoffDays = (int) \App\Models\Setting::where('key', 'lock_cutoff_days')->value('value') ?: 3;
        $logDate = \Carbon\Carbon::parse($dailyLog->date);
        if ($logDate->diffInDays(now()) > $cutoffDays && !auth()->user()->hasRole('Super Admin')) {
            return redirect()->route('daily-data.index')->with('error', "This log is older than {$cutoffDays} days and is locked.");
        }

        $request->validate([
            'date' => ['required', 'date'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'driver_id' => ['required', 'exists:drivers,id'],
            'pasgi_given' => ['required', 'numeric', 'min:0'],
            'daily_diesel_amount' => ['required', 'numeric', 'min:0'],
            'daily_diesel_liters' => ['required', 'numeric', 'min:0'],
            'main_km' => ['required', 'integer', 'min:0'],
            'local_km' => ['required', 'integer', 'min:0'],
            'remarks' => ['nullable', 'string'],
        ]);

        $dailyLog->update($request->all());

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('attachments', 'public');
            $dailyLog->attachments()->create([
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'created_by' => auth()->id(),
            ]);
        }

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'UPDATE',
            'model_type' => 'FleetDailyData',
            'model_id' => $dailyLog->id,
            'description' => "Updated Daily Data for Vehicle: " . $dailyLog->vehicle->vehicle_number . " on " . $dailyLog->date,
        ]);

        return redirect()->route('daily-data.index')->with('success', 'Daily log updated successfully.');
    }

    public function destroy(FleetDailyData $dailyLog)
    {
        $vehicleNumber = $dailyLog->vehicle->vehicle_number;
        $date = $dailyLog->date;
        $dailyLog->delete();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'DELETE',
            'model_type' => 'FleetDailyData',
            'model_id' => $dailyLog->id,
            'description' => "Deleted Daily Data for Vehicle: {$vehicleNumber} on {$date}",
        ]);

        return redirect()->route('daily-data.index')->with('success', 'Daily log deleted successfully.');
    }
}
