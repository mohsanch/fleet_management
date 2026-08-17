<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::pluck('value', 'key');
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'fuel_price_per_liter' => ['required', 'numeric', 'min:0'],
            'maintenance_interval_km' => ['required', 'integer', 'min:0'],
            'lock_cutoff_days' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($request->only(['fuel_price_per_liter', 'maintenance_interval_km', 'lock_cutoff_days']) as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        // Log this action
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => 'update',
            'model_type' => 'Setting',
            'description' => 'Updated global system settings (Fuel, Maintenance Interval, Cutoff days).',
        ]);

        return redirect()->back()->with('success', 'System settings updated successfully.');
    }
}
