@extends('layouts.app')

@section('title', 'Create New Order — MantraHeal CRM')

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="orderForm()">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('orders.index') }}" class="text-sm font-medium text-emerald-700 hover:text-emerald-800 flex items-center gap-1 mb-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Orders
            </a>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Create New Sales Order</h1>
        </div>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
            <div class="font-semibold mb-1">Please fix the following errors:</div>
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('orders.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Customer & Order Meta -->
        <div class="card p-6 grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">Select Customer *</label>
                <select name="customer_id" x-model="selectedCustomerId" @change="onCustomerChange()" required class="form-control w-full text-sm">
                    <option value="">-- Choose Existing Customer --</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" 
                                data-name="{{ $c->name }}" 
                                data-phone="{{ $c->phone }}" 
                                data-address="{{ $c->address_line1 }}" 
                                data-city="{{ $c->city }}" 
                                data-state="{{ $c->state }}" 
                                data-pincode="{{ $c->pincode }}"
                                {{ (old('customer_id', $selectedCustomer?->id) == $c->id) ? 'selected' : '' }}>
                            {{ $c->name }} ({{ $c->phone }}) — {{ $c->city ?? 'N/A' }}
                        </option>
                    @endforeach
                </select>
                <div class="mt-2 flex items-center justify-between text-xs text-slate-500">
                    <span>Don't see customer?</span>
                    <a href="{{ route('customers.create') }}" target="_blank" class="text-emerald-700 hover:underline font-medium">+ Add New Customer</a>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">Order Date *</label>
                <input type="date" name="order_date" value="{{ old('order_date', date('Y-m-d')) }}" required class="form-control w-full text-sm">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">Sales Channel *</label>
                <select name="channel" required class="form-control w-full text-sm">
                    <option value="direct" {{ old('channel') == 'direct' ? 'selected' : '' }}>Direct Phone / CRM</option>
                    <option value="website" {{ old('channel') == 'website' ? 'selected' : '' }}>MantraHeal Website</option>
                    <option value="shopify" {{ old('channel') == 'shopify' ? 'selected' : '' }}>Shopify Store</option>
                    <option value="whatsapp" {{ old('channel') == 'whatsapp' ? 'selected' : '' }}>WhatsApp Commerce</option>
                    <option value="meta_ads" {{ old('channel') == 'meta_ads' ? 'selected' : '' }}>Meta Ads / Instagram</option>
                    <option value="offline" {{ old('channel') == 'offline' ? 'selected' : '' }}>Offline Clinic / Retail</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">Order Status *</label>
                <select name="order_status" required class="form-control w-full text-sm">
                    <option value="New">New</option>
                    <option value="Confirmed" selected>Confirmed</option>
                    <option value="Processing">Processing</option>
                    <option value="Packed">Packed</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">Payment Method *</label>
                <select name="payment_method" required class="form-control w-full text-sm">
                    @foreach($paymentMethods as $pmKey => $pmVal)
                        <option value="{{ $pmKey }}">{{ $pmVal }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-2">Payment Status *</label>
                <select name="payment_status" required class="form-control w-full text-sm">
                    <option value="Pending">Pending (e.g. COD)</option>
                    <option value="Paid">Prepaid / Paid</option>
                    <option value="Partially Paid">Partial Payment</option>
                </select>
            </div>
        </div>

        <!-- Order Items Builder -->
        <div class="card p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-800">Order Items (Ayurvedic & Wellness Formulations)</h3>
                <button type="button" @click="addItem()" class="btn btn-secondary text-xs py-1.5 px-3 inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Product Row
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="table w-full text-sm">
                    <thead>
                        <tr>
                            <th class="w-1/2">Product / SKU</th>
                            <th class="w-24 text-center">Qty</th>
                            <th class="w-32 text-right">Unit Price (₹)</th>
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
                                        <option value="">-- Choose Product Formulation --</option>
                                        @foreach($products as $p)
                                            <option value="{{ $p->id }}" data-price="{{ $p->selling_price }}" data-gst="{{ $p->gst_rate }}">
                                                {{ $p->name }} (SKU: {{ $p->sku }}) — ₹{{ number_format($p->selling_price, 2) }}
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
                                           :name="'items[' + index + '][unit_price]'" 
                                           x-model.number="item.unit_price" 
                                           required 
                                           class="form-control text-right text-sm w-full">
                                </td>
                                <td class="text-right font-semibold text-slate-800 py-3">
                                    ₹<span x-text="(item.quantity * item.unit_price).toFixed(2)"></span>
                                </td>
                                <td class="text-center">
                                    <button type="button" @click="removeItem(index)" class="text-rose-500 hover:text-rose-700 p-1.5" :disabled="items.length <= 1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Price Breakdown Summary -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-slate-100">
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Coupon / Promo Code</label>
                        <input type="text" name="coupon_code" placeholder="e.g. MANTRA10" class="form-control text-sm w-full">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Discount Amount (₹)</label>
                        <input type="number" step="0.01" name="discount_amount" x-model.number="discount" min="0" class="form-control text-sm w-full">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Shipping Charges (₹)</label>
                        <input type="number" step="0.01" name="shipping_charge" x-model.number="shipping" min="0" class="form-control text-sm w-full">
                    </div>
                </div>

                <div class="bg-slate-50 p-5 rounded-xl space-y-2.5 text-sm">
                    <div class="flex justify-between text-slate-600">
                        <span>Items Subtotal:</span>
                        <span class="font-medium text-slate-800">₹<span x-text="subtotal().toFixed(2)"></span></span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Discount:</span>
                        <span class="text-emerald-700">- ₹<span x-text="discount.toFixed(2)"></span></span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Estimated GST (Inclusive/Tax):</span>
                        <span class="font-medium text-slate-800">₹<span x-text="gstEstimate().toFixed(2)"></span></span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Shipping / Delivery:</span>
                        <span class="font-medium text-slate-800">+ ₹<span x-text="shipping.toFixed(2)"></span></span>
                    </div>
                    <div class="border-t border-slate-200 pt-3 flex justify-between text-base font-bold text-slate-900">
                        <span>Grand Total Payable:</span>
                        <span class="text-emerald-800 text-lg">₹<span x-text="grandTotal().toFixed(2)"></span></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shipping & Logistics Information -->
        <div class="card p-6 space-y-4">
            <h3 class="font-bold text-slate-800 border-b border-slate-100 pb-3">Delivery & Shipping Address</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Recipient Full Name</label>
                    <input type="text" name="shipping_name" x-model="shippingName" class="form-control w-full text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Contact Phone</label>
                    <input type="text" name="shipping_phone" x-model="shippingPhone" class="form-control w-full text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Address Line 1 (Flat / Street) *</label>
                    <input type="text" name="shipping_address_line1" x-model="shippingAddress1" required class="form-control w-full text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Address Line 2 (Landmark)</label>
                    <input type="text" name="shipping_address_line2" class="form-control w-full text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">City *</label>
                    <input type="text" name="shipping_city" x-model="shippingCity" required class="form-control w-full text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">State *</label>
                    <input type="text" name="shipping_state" x-model="shippingState" required class="form-control w-full text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Pincode *</label>
                    <input type="text" name="shipping_pincode" x-model="shippingPincode" required class="form-control w-full text-sm">
                </div>
            </div>

            <!-- Logistics Partner Assignment -->
            <div class="pt-4 border-t border-slate-100 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Courier Partner</label>
                    <select name="courier_name" class="form-control w-full text-sm">
                        <option value="">-- Assign Later --</option>
                        @foreach($couriers as $courier)
                            <option value="{{ $courier }}">{{ $courier }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">AWB / Tracking Number (if available)</label>
                    <input type="text" name="tracking_number" placeholder="e.g. SR10928374" class="form-control w-full text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Customer / Packaging Notes</label>
                    <textarea name="notes" rows="2" placeholder="Fragile glass bottle handling, delivery time preference..." class="form-control w-full text-sm"></textarea>
                </div>
            </div>
        </div>

        <!-- Submit Action -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('orders.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary px-6 py-2.5 shadow-md">
                Create & Generate Order
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function orderForm() {
    return {
        selectedCustomerId: '{{ old('customer_id', $selectedCustomer?->id ?? '') }}',
        shippingName: '{{ old('shipping_name', $selectedCustomer?->name ?? '') }}',
        shippingPhone: '{{ old('shipping_phone', $selectedCustomer?->phone ?? '') }}',
        shippingAddress1: '{{ old('shipping_address_line1', $selectedCustomer?->address_line1 ?? '') }}',
        shippingCity: '{{ old('shipping_city', $selectedCustomer?->city ?? '') }}',
        shippingState: '{{ old('shipping_state', $selectedCustomer?->state ?? '') }}',
        shippingPincode: '{{ old('shipping_pincode', $selectedCustomer?->pincode ?? '') }}',
        discount: {{ old('discount_amount', 0) }},
        shipping: {{ old('shipping_charge', 0) }},
        items: [
            { product_id: '', quantity: 1, unit_price: 0, gst: 18 }
        ],
        addItem() {
            this.items.push({ product_id: '', quantity: 1, unit_price: 0, gst: 18 });
        },
        removeItem(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        },
        onProductSelect(index) {
            const selectEl = event.target;
            const selectedOpt = selectEl.options[selectEl.selectedIndex];
            if (selectedOpt && selectedOpt.dataset.price) {
                this.items[index].unit_price = parseFloat(selectedOpt.dataset.price) || 0;
                this.items[index].gst = parseFloat(selectedOpt.dataset.gst) || 18;
            }
        },
        onCustomerChange() {
            const selectEl = event.target;
            const opt = selectEl.options[selectEl.selectedIndex];
            if (opt) {
                this.shippingName = opt.dataset.name || '';
                this.shippingPhone = opt.dataset.phone || '';
                this.shippingAddress1 = opt.dataset.address || '';
                this.shippingCity = opt.dataset.city || '';
                this.shippingState = opt.dataset.state || '';
                this.shippingPincode = opt.dataset.pincode || '';
            }
        },
        subtotal() {
            return this.items.reduce((sum, item) => sum + ((item.quantity || 0) * (item.unit_price || 0)), 0);
        },
        gstEstimate() {
            const net = Math.max(0, this.subtotal() - this.discount);
            return net * 0.18;
        },
        grandTotal() {
            const net = Math.max(0, this.subtotal() - this.discount);
            return net + (this.shipping || 0);
        }
    }
}
</script>
@endpush
@endsection
