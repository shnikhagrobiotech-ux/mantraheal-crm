@extends('layouts.app')

@section('title', 'Product Catalog — MantraHeal CRM')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Product Catalog</h1>
            <p class="text-sm text-slate-500">Manage MantraHeal formulations, pricing, SKUs, and categories</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('categories.index') }}" class="btn btn-secondary inline-flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                Categories
            </a>
            <a href="{{ route('products.create') }}" class="btn btn-primary inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add New Product
            </a>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="card p-4">
        <form method="GET" action="{{ route('products.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div class="lg:col-span-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, SKU, or barcode..." 
                       class="form-control text-sm w-full">
            </div>
            <div>
                <select name="category" class="form-control text-sm w-full">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="btn btn-primary text-sm py-2 px-4 flex-1">Filter</button>
                <a href="{{ route('products.index') }}" class="btn btn-secondary text-sm py-2 px-3">Reset</a>
            </div>
        </form>
    </div>

    <!-- Product Grid / Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead>
                    <tr>
                        <th>Product & Formulation</th>
                        <th>SKU</th>
                        <th>Category</th>
                        <th class="text-right">MRP (₹)</th>
                        <th class="text-right">Selling Price (₹)</th>
                        <th class="text-right">Purchase Price (₹)</th>
                        <th class="text-center">Stock</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $product)
                        <tr class="hover:bg-slate-50/75 transition-colors">
                            <td>
                                <div class="font-semibold text-slate-800">
                                    <a href="{{ route('products.show', $product) }}" class="hover:text-emerald-700">
                                        {{ $product->name }}
                                    </a>
                                </div>
                                <div class="text-xs text-slate-400 font-mono">{{ $product->product_code }}</div>
                            </td>
                            <td class="font-mono text-xs text-slate-600 font-medium">
                                {{ $product->sku }}
                            </td>
                            <td>
                                <span class="badge bg-emerald-50 text-emerald-800 text-xs">
                                    {{ $product->category?->name ?? 'Uncategorized' }}
                                </span>
                            </td>
                            <td class="text-right text-slate-400 text-xs line-through">
                                ₹{{ number_format($product->mrp, 2) }}
                            </td>
                            <td class="text-right font-semibold text-slate-800">
                                ₹{{ number_format($product->selling_price, 2) }}
                            </td>
                            <td class="text-right text-slate-600 text-xs">
                                ₹{{ number_format($product->purchase_price, 2) }}
                            </td>
                            <td class="text-center">
                                @php
                                    $stock = $product->stock_quantity ?? 0;
                                    $minStock = $product->minimum_stock ?? 10;
                                @endphp
                                @if($stock <= 0)
                                    <span class="badge badge-danger text-xs">Out of Stock</span>
                                @elseif($stock <= $minStock)
                                    <span class="badge badge-warning text-xs">{{ $stock }} (Low)</span>
                                @else
                                    <span class="badge badge-success text-xs font-semibold">{{ $stock }} in stock</span>
                                @endif
                            </td>
                            <td>
                                @if($product->is_active)
                                    <span class="badge badge-success text-[11px]">Active</span>
                                @else
                                    <span class="badge badge-secondary text-[11px]">Inactive</span>
                                @endif
                            </td>
                            <td class="text-right whitespace-nowrap space-x-1">
                                <a href="{{ route('products.show', $product) }}" class="btn btn-secondary text-xs py-1 px-2.5">
                                    View
                                </a>
                                <a href="{{ route('products.edit', $product) }}" class="btn btn-secondary text-xs py-1 px-2.5">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-12 text-slate-500">
                                <div class="max-w-sm mx-auto space-y-3">
                                    <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 mx-auto flex items-center justify-center">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    </div>
                                    <div class="font-medium text-slate-800">No products found</div>
                                    <p class="text-xs text-slate-500">Get started by creating your first wellness or herbal formulation product.</p>
                                    <a href="{{ route('products.create') }}" class="btn btn-primary text-xs py-1.5 px-3">Add First Product</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
