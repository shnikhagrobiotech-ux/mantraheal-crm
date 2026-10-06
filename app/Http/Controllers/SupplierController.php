<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::withCount('purchaseOrders');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('supplier_code', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $suppliers = $query->paginate(15)->withQueryString();
        return view('purchase.suppliers', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'gstin' => 'nullable|string|max:20',
            'address' => 'required|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'payment_terms' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $code = 'SUP-' . strtoupper(Str::random(5));
        $validated['supplier_code'] = $code;
        $validated['status'] = 'active';

        $supplier = Supplier::create($validated);
        AuditLog::log('created', $supplier, "Supplier {$supplier->name} ({$supplier->supplier_code}) created");

        return back()->with('success', 'Supplier created successfully.');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load(['purchaseOrders.items', 'goodsReceivedNotes.items']);
        return view('purchase.supplier-show', compact('supplier'));
    }
}
