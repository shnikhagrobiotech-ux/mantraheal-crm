@extends('layouts.app')

@section('title', 'Add New Product — MantraHeal CRM')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div>
        <a href="{{ route('products.index') }}" class="text-sm font-medium text-emerald-700 hover:text-emerald-800 flex items-center gap-1 mb-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Catalog
        </a>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Add New Product Formulation</h1>
        <p class="text-sm text-slate-500">Configure new healthcare, ayurvedic, or wellness product SKU</p>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
            <div class="font-semibold mb-1">Please fix the following validation errors:</div>
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('products.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Core Information -->
        <div class="card p-6 space-y-4">
            <h2 class="font-bold text-slate-800 text-base border-b border-slate-100 pb-3">Basic Information</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Product Title / Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. MantraHeal Ashwagandha Gold Capsules" required class="form-control w-full text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">SKU Code *</label>
                    <input type="text" name="sku" value="{{ old('sku') }}" placeholder="e.g. MH-ASHWA-60" required class="form-control w-full text-sm font-mono">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Category *</label>
                    <select name="category_id" required class="form-control w-full text-sm">
                        <option value="">-- Select Category --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Brand Name *</label>
                    <input type="text" name="brand" value="{{ old('brand', 'MantraHeal') }}" required class="form-control w-full text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Barcode / EAN</label>
                    <input type="text" name="barcode" value="{{ old('barcode') }}" placeholder="890123456789" class="form-control w-full text-sm font-mono">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Product Description / Highlights</label>
                    <textarea name="description" rows="3" placeholder="Formulation details, dosage instructions, organic ingredients..." class="form-control w-full text-sm">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Pricing & GST Details -->
        <div class="card p-6 space-y-4">
            <h2 class="font-bold text-slate-800 text-base border-b border-slate-100 pb-3">Pricing, Tax & HSN</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">MRP (₹) *</label>
                    <input type="number" step="0.01" name="mrp" value="{{ old('mrp') }}" required class="form-control w-full text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Selling Price (₹) *</label>
                    <input type="number" step="0.01" name="selling_price" value="{{ old('selling_price') }}" required class="form-control w-full text-sm font-bold text-emerald-800">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Purchase / Cost Price (₹) *</label>
                    <input type="number" step="0.01" name="purchase_price" value="{{ old('purchase_price') }}" required class="form-control w-full text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">GST Rate (%) *</label>
                    <select name="gst_percent" required class="form-control w-full text-sm">
                        <option value="12" {{ old('gst_percent', '12') == '12' ? 'selected' : '' }}>12% (Ayurvedic / OTC)</option>
                        <option value="5" {{ old('gst_percent') == '5' ? 'selected' : '' }}>5% (Raw Herbs)</option>
                        <option value="18" {{ old('gst_percent') == '18' ? 'selected' : '' }}>18% (Supplements/Cosmetics)</option>
                        <option value="0" {{ old('gst_percent') == '0' ? 'selected' : '' }}>0% (Exempt)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">HSN Code</label>
                    <input type="text" name="hsn_code" value="{{ old('hsn_code', '30049011') }}" placeholder="30049011" class="form-control w-full text-sm font-mono">
                </div>
            </div>
        </div>

        <!-- Inventory Thresholds & Status -->
        <div class="card p-6 space-y-4">
            <h2 class="font-bold text-slate-800 text-base border-b border-slate-100 pb-3">Stock Thresholds & Status</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Minimum Buffer Stock *</label>
                    <input type="number" name="minimum_stock" value="{{ old('minimum_stock', 15) }}" min="0" required class="form-control w-full text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Reorder Level *</label>
                    <input type="number" name="reorder_level" value="{{ old('reorder_level', 30) }}" min="0" required class="form-control w-full text-sm">
                </div>

                <div class="flex items-center pt-5">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="w-4 h-4 text-emerald-700 rounded border-slate-300">
                        <span class="text-sm font-medium text-slate-800">Publish as Active Product</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('products.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary px-6 py-2.5">
                Save & Create Product
            </button>
        </div>
    </form>
</div>
@endsection
