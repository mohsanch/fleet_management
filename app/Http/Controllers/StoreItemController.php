<?php

namespace App\Http\Controllers;

use App\Models\StoreItem;
use App\Models\Vehicle;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class StoreItemController extends Controller implements HasMiddleware
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
$query = StoreItem::with(['vehicle', 'creator']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('item_name', 'like', '%' . $search . '%')
                  ->orWhere('vendor', 'like', '%' . $search . '%')
                  ->orWhere('remarks', 'like', '%' . $search . '%')
                  ->orWhereHas('vehicle', function($vq) use ($search) {
                      $vq->where('vehicle_number', 'like', '%' . $search . '%');
                  });
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        $query->when($request->filled('date_from'), fn($q) => $q->whereDate('date', '>=', $request->date_from))
              ->when($request->filled('date_to'), fn($q) => $q->whereDate('date', '<=', $request->date_to));

        $storeItems = $query->orderBy('date', 'desc')->paginate(10)->withQueryString();

        return view('store_items.index', compact('storeItems'));
    }

    public function create()
    {
        $vehicles = Vehicle::where('status', 'active')->orderBy('vehicle_number')->get();
        return view('store_items.create', compact('vehicles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'item_name' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
            'amount' => ['required', 'numeric', 'min:0'],
            'date' => ['required', 'date'],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'vendor' => ['required', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
        ]);

        $data = $request->all();
        $data['created_by'] = auth()->id();

        $storeItem = StoreItem::create($data);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'CREATE',
            'model_type' => 'StoreItem',
            'model_id' => $storeItem->id,
            'description' => "Logged Store Item Purchase: " . $storeItem->item_name . " (Qty: " . $storeItem->quantity . ")",
        ]);

        return redirect()->route('store-items.index')->with('success', 'Store item added successfully.');
    }

    public function edit(StoreItem $storeItem)
    {
        $vehicles = Vehicle::where('status', 'active')->orWhere('id', $storeItem->vehicle_id)->orderBy('vehicle_number')->get();
        return view('store_items.edit', compact('storeItem', 'vehicles'));
    }

    public function update(Request $request, StoreItem $storeItem)
    {
        $request->validate([
            'item_name' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
            'amount' => ['required', 'numeric', 'min:0'],
            'date' => ['required', 'date'],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'vendor' => ['required', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
        ]);

        $storeItem->update($request->all());

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'UPDATE',
            'model_type' => 'StoreItem',
            'model_id' => $storeItem->id,
            'description' => "Updated Store Item: " . $storeItem->item_name,
        ]);

        return redirect()->route('store-items.index')->with('success', 'Store item updated successfully.');
    }

    public function destroy(StoreItem $storeItem)
    {
        $name = $storeItem->item_name;
        $storeItem->delete();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'DELETE',
            'model_type' => 'StoreItem',
            'model_id' => $storeItem->id,
            'description' => "Deleted Store Item: {$name}",
        ]);

        return redirect()->route('store-items.index')->with('success', 'Store item deleted successfully.');
    }
}
