@extends('layouts.app')

@section('title', 'Create Purchase Order — MantraHeal CRM')

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="poForm()">
    <!-- Header -->
    <div>
        <a href="{{ route('purchase-orders.index') }}" class="text-sm font-medium text-emerald-700 hover:text-emerald-800 flex items-center gap-1 mb-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Purchase Orders
        </a>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Issue Purchase Order</h1>
        <p class="text-sm text-slate-500">Procure bulk raw herbs and Ayurvedic packaging materials</p>
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

    <form action="{{ route('purchase-orders.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- PO Meta -->
        <div class="card p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Supplier *</label>
                <select name="supplier_id" required class="form-control w-full text-sm">
                    <option value="">-- Select Vendor --</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}" {{ (old('supplier_id', request('supplier_id')) == $s->id) ? 'selected' : '' }}>
                            {{ $s->name }} ({{ $s->supplier_code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Receiving Warehouse *</label>
                <select name="warehouse_id" required class="form-control w-full text-sm">
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ $wh->is_default ? 'selected' : '' }}>{{ $wh->name }} ({{ $wh->city }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">PO Issue Date *</label>
                <input type="date" name="po_date" value="{{ old('po_date', date('Y-m-d')) }}" required class="form-control w-full text-sm">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Expected Delivery</label>
                <input type="date" name="expected_delivery_date" value="{{ old('expected_delivery_date', date('Y-m-d', strtotime('+14 days'))) }}" class="form-control w-full text-sm">
            </div>

            <div class="lg:col-span-4">
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Procurement Notes / Quality Specs</label>
                <input type="text" name="notes" placeholder="Batch purity COA required, food-grade airtight drums, cold transport..." class="form-control w-full text-sm">
            </div>
        </div>

        <!-- Items Table -->
        <div class="card p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-800">Procurement Items</h3>
                <button type="button" @click="addItem()" class="btn btn-secondary text-xs py-1 px-3">
                    + Add Product Line
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="table w-full text-sm">
                    <thead>
                        <tr>
                            <th class="w-1/2">Product Formulation</th>
                            <th class="w-28 text-center">Quantity</th>
                            <th class="w-32 text-right">Unit Rate (₹)</th>
                            <th class="w-24 text-right">GST %</th>
                            <th class="w-32 text-right">Total (₹)</th>
                            <th class="w-16 text-center">Remove</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(item, index) in items" :key="index">
                            <tr>
                                <td>
                                    <select :name="'items[' + index + '][product_id]'" 
                                            x-model="item.product_id" 
                                            @change="onProductSelect(index)" 
                                            required 
                                            class="form-control w-full text-sm">
                                        <option value="">-- Choose Product --</option>
                                        @foreach($products as $p)
                                            <option value="{{ $p->id }}" data-rate="{{ $p->purchase_price }}" data-gst="{{ $p->gst_percent }}">
                                                {{ $p->name }} (SKU: {{ $p->sku }})
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" 
                                           :name="'items[' + index + '][quantity]'" 
                                           x-model.number="item.quantity" 
                                           min="1" 
                                           required 
                                           class="form-control text-center text-sm w-full">
                                </td>
                                <td>
                                    <input type="number" 
                                           step="0.01" 
                                           :name="'items[' + index + '][rate]'" 
                                           x-model.number="item.rate" 
                                           required 
                                           class="form-control text-right text-sm w-full">
                                </td>
                                <td>
                                    <input type="number" 
                                           step="0.01" 
                                           :name="'items[' + index + '][tax_percent]'" 
                                           x-model.number="item.tax_percent" 
                                           class="form-control text-right text-sm w-full">
                                </td>
                                <td class="text-right font-bold text-slate-800 py-3">
                                    ₹<span x-text="((item.quantity * item.rate) * (1 + (item.tax_percent / 100))).toFixed(2)"></span>
                                </td>
                                <td class="text-center">
                                    <button type="button" @click="removeItem(index)" class="text-rose-500 hover:text-rose-700 p-1" :disabled="items.length <= 1">
                                        &times;
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Grand Total -->
            <div class="flex justify-end pt-4 border-t border-slate-100">
                <div class="text-right space-y-1">
                    <div class="text-xs text-slate-500">Estimated Total Order Value (incl. GST):</div>
                    <div class="text-2xl font-bold text-emerald-800">
                        ₹<span x-text="totalOrder().toFixed(2)"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary px-6 py-2.5">
                Generate & Issue Purchase Order
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function poForm() {
    return {
        items: [
            { product_id: '', quantity: 100, rate: 0, tax_percent: 12 }
        ],
        addItem() {
            this.items.push({ product_id: '', quantity: 50, rate: 0, tax_percent: 12 });
        },
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        },
        onProductSelect(index) {
            const selectEl = event.target;
            const selectedOpt = selectEl.options[selectEl.selectedIndex];
            if (selectedOpt && selectedOpt.dataset.rate) {
                this.items[index].rate = parseFloat(selectedOpt.dataset.rate) || 0;
                this.items[index].tax_percent = parseFloat(selectedOpt.dataset.gst) || 12;
            }
        },
        totalOrder() {
            return this.items.reduce((sum, item) => {
                const sub = (item.quantity || 0) * (item.rate || 0);
                const tax = sub * ((item.tax_percent || 0) / 100);
                return sum + sub + tax;
            }, 0);
        }
    }
}
</script>
@endpush
@endsection
