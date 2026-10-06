<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WarehouseController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::withCount('stockBalances')->get();
        return view('warehouses.index', compact('warehouses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:warehouses,name',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'pincode' => 'required|string|max:10',
            'is_default' => 'boolean',
        ]);

        $code = 'WH-' . strtoupper(Str::slug($validated['name']));
        $validated['code'] = $code;
        $validated['is_active'] = true;
        $validated['is_default'] = $request->has('is_default');

        if ($validated['is_default']) {
            Warehouse::where('is_default', true)->update(['is_default' => false]);
        }

        $wh = Warehouse::create($validated);
        AuditLog::log('created', $wh, "Warehouse {$wh->name} ({$wh->code}) created");

        return back()->with('success', 'Warehouse created successfully.');
    }

    public function show(Warehouse $warehouse)
    {
        $warehouse->load(['stockBalances.product.category', 'batches.product', 'stockMovements.product']);
        return view('warehouses.show', compact('warehouse'));
    }
}
