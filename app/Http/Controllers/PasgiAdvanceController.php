<?php

namespace App\Http\Controllers;

use App\Models\PasgiAdvance;
use App\Models\PasgiAdjustment;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PasgiAdvanceController extends Controller implements HasMiddleware
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
$query = PasgiAdvance::with(['driver', 'vehicle', 'creator']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('remarks', 'like', '%' . $search . '%')
                  ->orWhereHas('driver', function($dq) use ($search) {
                      $dq->where('name', 'like', '%' . $search . '%');
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
              ->when($request->filled('driver_id'), fn($q) => $q->where('driver_id', $request->driver_id))
              ->when($request->filled('vehicle_id'), fn($q) => $q->where('vehicle_id', $request->vehicle_id));

        $advances = $query->orderBy('date', 'desc')->paginate(10)->withQueryString();
        $drivers = Driver::orderBy('name')->get();
        $vehicles = Vehicle::orderBy('vehicle_number')->get();

        return view('pasgi_advances.index', compact('advances', 'drivers', 'vehicles'));
    }

    public function create()
    {
        $drivers  = Driver::where('status', 'active')->orderBy('name')->get();
        $vehicles = Vehicle::where('status', 'active')->orderBy('vehicle_number')->get();
        return view('pasgi_advances.create', compact('drivers', 'vehicles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'driver_id'  => ['required', 'exists:drivers,id'],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'amount'     => ['required', 'numeric', 'min:1'],
            'date'       => ['required', 'date'],
            'remarks'    => ['nullable', 'string'],
        ]);

        $data = $request->all();
        $data['created_by'] = auth()->id();

        $advance = PasgiAdvance::create($data);

        ActivityLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'CREATE',
            'model_type'  => 'PasgiAdvance',
            'model_id'    => $advance->id,
            'description' => "Issued Pasgi Advance Rs. " . number_format($advance->amount) . " to Driver: " . $advance->driver->name,
        ]);

        return redirect()->route('pasgi-advances.index')->with('success', 'Pasgi advance issued successfully.');
    }

    public function edit(PasgiAdvance $pasgiAdvance)
    {
        $drivers  = Driver::where('status', 'active')->orWhere('id', $pasgiAdvance->driver_id)->orderBy('name')->get();
        $vehicles = Vehicle::where('status', 'active')->orWhere('id', $pasgiAdvance->vehicle_id)->orderBy('vehicle_number')->get();
        return view('pasgi_advances.edit', compact('pasgiAdvance', 'drivers', 'vehicles'));
    }

    public function update(Request $request, PasgiAdvance $pasgiAdvance)
    {
        $request->validate([
            'driver_id'  => ['required', 'exists:drivers,id'],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'amount'     => ['required', 'numeric', 'min:1'],
            'date'       => ['required', 'date'],
            'remarks'    => ['nullable', 'string'],
        ]);

        $pasgiAdvance->update($request->all());

        ActivityLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'UPDATE',
            'model_type'  => 'PasgiAdvance',
            'model_id'    => $pasgiAdvance->id,
            'description' => "Updated Pasgi Advance #{$pasgiAdvance->id} for Driver: " . $pasgiAdvance->driver->name,
        ]);

        return redirect()->route('pasgi-advances.index')->with('success', 'Pasgi advance updated successfully.');
    }

    public function destroy(PasgiAdvance $pasgiAdvance)
    {
        $name = $pasgiAdvance->driver->name;
        $amount = $pasgiAdvance->amount;
        $pasgiAdvance->delete();

        ActivityLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'DELETE',
            'model_type'  => 'PasgiAdvance',
            'model_id'    => $pasgiAdvance->id,
            'description' => "Deleted Pasgi Advance for {$name}: Rs. " . number_format($amount),
        ]);

        return redirect()->route('pasgi-advances.index')->with('success', 'Pasgi advance deleted.');
    }

    /**
     * Show the balance summary for a specific driver + form to record recovery.
     */
    public function driverBalance(Driver $driver)
    {
        $advances     = PasgiAdvance::where('driver_id', $driver->id)->orderBy('date', 'desc')->get();
        $adjustments  = PasgiAdjustment::where('driver_id', $driver->id)->orderBy('date', 'desc')->get();
        $totalGiven   = $advances->sum('amount');
        $totalRecovered = $adjustments->sum('amount');
        $outstanding  = max(0, $totalGiven - $totalRecovered);

        return view('pasgi_advances.driver_balance', compact('driver', 'advances', 'adjustments', 'totalGiven', 'totalRecovered', 'outstanding'));
    }

    /**
     * Record a Pasgi recovery / adjustment against a driver.
     */
    public function storeAdjustment(Request $request)
    {
        $request->validate([
            'driver_id' => ['required', 'exists:drivers,id'],
            'amount'    => ['required', 'numeric', 'min:1'],
            'date'      => ['required', 'date'],
            'remarks'   => ['nullable', 'string', 'max:500'],
        ]);

        $adjustment = PasgiAdjustment::create([
            'driver_id'  => $request->driver_id,
            'amount'     => $request->amount,
            'date'       => $request->date,
            'remarks'    => $request->remarks,
            'created_by' => auth()->id(),
        ]);

        ActivityLog::create([
            'user_id'     => auth()->id(),
            'action'      => 'CREATE',
            'model_type'  => 'PasgiAdjustment',
            'model_id'    => $adjustment->id,
            'description' => "Recorded Pasgi Recovery Rs. " . number_format($adjustment->amount) . " from Driver ID: {$adjustment->driver_id}",
        ]);

        return redirect()->route('pasgi-advances.driver-balance', $request->driver_id)
            ->with('success', 'Pasgi recovery recorded successfully.');
    }
}
