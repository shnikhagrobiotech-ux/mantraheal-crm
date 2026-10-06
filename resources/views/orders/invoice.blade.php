<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TAX INVOICE - {{ $order->order_number }} - {{ \App\Models\Setting::get('company_name', 'MantraHeal') }}</title>
    <link rel="stylesheet" href="{{ asset('css/tailwind.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/mantraheal.css') }}">
    <style>
        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm;
            }
            .no-print {
                display: none !important;
            }
            body {
                background-color: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
                font-size: 10pt !important;
                color: #0f172a !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .invoice-box {
                box-shadow: none !important;
                border: 1px solid #cbd5e1 !important;
                border-radius: 0 !important;
                padding: 1.5rem !important;
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
            }
        }
    </style>
</head>
<body class="bg-slate-100 font-sans antialiased text-slate-800 p-4 sm:p-8">

    @php
        $companyState = \App\Models\Setting::get('company_state', 'Haryana');
        $customerState = trim($order->shipping_state ?? 'Haryana');
        $isIntraState = strcasecmp(trim($companyState), $customerState) === 0;
        $taxAmount = (float) $order->tax_amount;
        $cgst = $isIntraState ? ($taxAmount / 2) : 0;
        $sgst = $isIntraState ? ($taxAmount / 2) : 0;
        $igst = !$isIntraState ? $taxAmount : 0;
        $totalAmount = (float) $order->total_amount;
        $amountInWords = \App\Helpers\NumberToWords::convert($totalAmount);
    @endphp

    <!-- Top Action Bar (Screen Only) -->
    <div class="max-w-4xl mx-auto mb-4 flex items-center justify-between no-print">
        <a href="{{ route('orders.show', $order) }}" class="btn btn-secondary text-xs inline-flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Order
        </a>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="btn btn-primary text-xs inline-flex items-center gap-1.5 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print / Save as PDF
            </button>
        </div>
    </div>

    <!-- Printable GST Invoice Container -->
    <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-md border border-slate-200 p-8 sm:p-10 invoice-box space-y-6">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start gap-4 border-b border-slate-200 pb-5">
            <div>
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-teal-800 text-amber-300 flex items-center justify-center font-bold text-xl shadow-sm">
                        MH
                    </div>
                    <div>
                        <div class="text-2xl font-black text-slate-900 tracking-tight uppercase">{{ \App\Models\Setting::get('company_name', 'MantraHeal') }}</div>
                        <div class="text-xs font-semibold text-teal-800 uppercase tracking-wider">{{ \App\Models\Setting::get('tagline', 'Ayurvedic & Natural Wellness Private Limited') }}</div>
                    </div>
                </div>
                <div class="mt-3 text-xs text-slate-600 space-y-0.5">
                    <div>{{ \App\Models\Setting::get('company_address', 'Plot No. 42, Sector 18, Udyog Vihar, Gurugram, Haryana, 122015, India') }}</div>
                    <div><strong>GSTIN:</strong> {{ \App\Models\Setting::get('gstin', '06AABCM1234F1Z8') }} | <strong>PAN:</strong> {{ \App\Models\Setting::get('pan_number', 'AABCM1234F') }}</div>
                    <div><strong>Email:</strong> {{ \App\Models\Setting::get('support_email', 'support@mantraheal.com') }} | <strong>Helpline:</strong> {{ \App\Models\Setting::get('support_phone', '+91 98765 43210') }}</div>
                </div>
            </div>

            <div class="text-left sm:text-right">
                <span class="inline-block px-3 py-1 rounded bg-teal-50 text-teal-900 text-xs font-bold uppercase tracking-wider mb-2 border border-teal-200">
                    TAX INVOICE
                </span>
                <div class="text-xs text-slate-500">Invoice Number</div>
                <div class="font-mono text-base font-bold text-slate-900">INV-{{ $order->order_number }}</div>
                <div class="mt-2 text-xs text-slate-500">Invoice Date</div>
                <div class="font-medium text-slate-800 text-sm">{{ $order->created_at->format('d F Y') }}</div>
                <div class="mt-1 text-xs text-slate-500">
                    Place of Supply: <strong>{{ $order->shipping_state ?? 'Haryana' }}</strong> 
                    <span class="text-slate-400">({{ $isIntraState ? 'Intra-State / CGST+SGST' : 'Inter-State / IGST' }})</span>
                </div>
            </div>
        </div>

        <!-- Billing & Shipping Parties -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 text-xs border-b border-slate-200 pb-5">
            <div class="p-3 bg-slate-50 rounded-lg border border-slate-200/80">
                <div class="font-bold text-slate-700 uppercase tracking-wider mb-1.5 text-[11px] flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Billed To (Customer):
                </div>
                <div class="font-bold text-sm text-slate-900">{{ $order->customer?->name ?? $order->shipping_name }}</div>
                <div class="text-slate-600 mt-1 space-y-0.5">
                    <div>{{ $order->shipping_address_line1 }}</div>
                    @if($order->shipping_address_line2) <div>{{ $order->shipping_address_line2 }}</div> @endif
                    <div>{{ $order->shipping_city }}, {{ $order->shipping_state }} - {{ $order->shipping_pincode }}</div>
                    <div><strong>Mobile:</strong> {{ $order->customer?->phone ?? $order->shipping_phone }}</div>
                    @if($order->customer?->email) <div><strong>Email:</strong> {{ $order->customer->email }}</div> @endif
                </div>
            </div>

            <div class="p-3 bg-slate-50 rounded-lg border border-slate-200/80">
                <div class="font-bold text-slate-700 uppercase tracking-wider mb-1.5 text-[11px] flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Shipped Destination:
                </div>
                <div class="font-bold text-sm text-slate-900">{{ $order->shipping_name ?? $order->customer?->name }}</div>
                <div class="text-slate-600 mt-1 space-y-0.5">
                    <div>{{ $order->shipping_address_line1 }}</div>
                    @if($order->shipping_address_line2) <div>{{ $order->shipping_address_line2 }}</div> @endif
                    <div>{{ $order->shipping_city }}, {{ $order->shipping_state }} - {{ $order->shipping_pincode }}</div>
                    <div><strong>Logistics Carrier:</strong> {{ $order->courier_name ?? 'Delhivery Express' }}</div>
                    <div><strong>AWB / Tracking Number:</strong> {{ $order->tracking_number ?? 'In Transit' }}</div>
                </div>
            </div>
        </div>

        <!-- Line Items Table -->
        <div>
            <table class="w-full text-xs text-left">
                <thead>
                    <tr class="border-b-2 border-slate-300 text-slate-700 uppercase text-[10px] font-bold bg-slate-50">
                        <th class="py-2.5 px-2">#</th>
                        <th class="py-2.5 px-2">Product Description</th>
                        <th class="py-2.5 px-2 text-center">HSN Code</th>
                        <th class="py-2.5 px-2 text-center">Qty</th>
                        <th class="py-2.5 px-2 text-right">Unit Rate (₹)</th>
                        <th class="py-2.5 px-2 text-right">Taxable Val (₹)</th>
                        @if($isIntraState)
                            <th class="py-2.5 px-2 text-right">CGST</th>
                            <th class="py-2.5 px-2 text-right">SGST</th>
                        @else
                            <th class="py-2.5 px-2 text-right">IGST</th>
                        @endif
                        <th class="py-2.5 px-2 text-right">Total (₹)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($order->items as $idx => $item)
                        @php
                            $taxRate = $item->tax_rate ?? 18;
                            $taxableVal = $item->total_price / (1 + ($taxRate / 100));
                            $itemTax = $item->total_price - $taxableVal;
                        @endphp
                        <tr>
                            <td class="py-2.5 px-2 text-slate-400 font-mono">{{ $idx + 1 }}</td>
                            <td class="py-2.5 px-2">
                                <div class="font-semibold text-slate-900">{{ $item->product_name }}</div>
                                <div class="text-[10px] text-slate-500 font-mono">SKU: {{ $item->sku ?? $item->product?->sku ?? 'N/A' }}</div>
                            </td>
                            <td class="py-2.5 px-2 text-center font-mono text-slate-600">{{ $item->hsn_code ?? '30049011' }}</td>
                            <td class="py-2.5 px-2 text-center font-bold text-slate-800">{{ $item->quantity }}</td>
                            <td class="py-2.5 px-2 text-right text-slate-700">₹{{ number_format($item->unit_price, 2) }}</td>
                            <td class="py-2.5 px-2 text-right text-slate-700">₹{{ number_format($taxableVal, 2) }}</td>
                            @if($isIntraState)
                                <td class="py-2.5 px-2 text-right text-slate-600">₹{{ number_format($itemTax / 2, 2) }} <span class="text-[9px] text-slate-400">({{ $taxRate / 2 }}%)</span></td>
                                <td class="py-2.5 px-2 text-right text-slate-600">₹{{ number_format($itemTax / 2, 2) }} <span class="text-[9px] text-slate-400">({{ $taxRate / 2 }}%)</span></td>
                            @else
                                <td class="py-2.5 px-2 text-right text-slate-600">₹{{ number_format($itemTax, 2) }} <span class="text-[9px] text-slate-400">({{ $taxRate }}%)</span></td>
                            @endif
                            <td class="py-2.5 px-2 text-right font-bold text-slate-900">₹{{ number_format($item->total_price, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals & Taxes Breakdown -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-3 border-t border-slate-200 text-xs">
            <div class="space-y-3">
                <!-- Payment & Channel Meta -->
                <div class="bg-slate-50 p-3 rounded-lg border border-slate-200 space-y-1">
                    <div class="font-bold text-slate-700 uppercase tracking-wider text-[10px] mb-1">Payment Details</div>
                    <div class="text-slate-700"><strong>Payment Method:</strong> {{ strtoupper($order->payment_method ?? 'COD') }}</div>
                    <div class="text-slate-700"><strong>Payment Status:</strong> <span class="uppercase font-bold text-teal-800">{{ $order->payment_status }}</span></div>
                    <div class="text-slate-700"><strong>Sales Channel:</strong> {{ ucfirst($order->channel ?? $order->sales_channel ?? 'Direct Phone') }}</div>
                </div>

                <!-- Corporate Bank Details -->
                <div class="bg-teal-50/60 p-3 rounded-lg border border-teal-200/80 text-slate-700">
                    <div class="font-bold text-teal-950 uppercase tracking-wider text-[10px] mb-1.5 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-teal-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        Bank Transfer & NEFT Details
                    </div>
                    <div class="grid grid-cols-2 gap-x-3 gap-y-1 text-[11px]">
                        <div><strong>Bank Name:</strong> {{ \App\Models\Setting::get('bank_name', 'HDFC Bank Ltd.') }}</div>
                        <div><strong>Account No:</strong> {{ \App\Models\Setting::get('bank_account_no', '50200012345678') }}</div>
                        <div><strong>IFSC Code:</strong> {{ \App\Models\Setting::get('bank_ifsc', 'HDFC0001234') }}</div>
                        <div><strong>Branch:</strong> {{ \App\Models\Setting::get('bank_branch', 'Cyber City, Gurugram') }}</div>
                    </div>
                </div>
            </div>

            <div class="space-y-2 text-sm bg-slate-50/50 p-4 rounded-lg border border-slate-200">
                <div class="flex justify-between text-slate-600">
                    <span>Taxable Subtotal:</span>
                    <span class="font-semibold text-slate-800">₹{{ number_format($order->subtotal - $taxAmount, 2) }}</span>
                </div>
                @if($order->discount_amount > 0)
                    <div class="flex justify-between text-emerald-700">
                        <span>Discount / Offer:</span>
                        <span>- ₹{{ number_format($order->discount_amount, 2) }}</span>
                    </div>
                @endif

                @if($isIntraState)
                    <div class="flex justify-between text-slate-600">
                        <span>CGST (Central Tax):</span>
                        <span class="font-semibold text-slate-800">₹{{ number_format($cgst, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>SGST (State Tax):</span>
                        <span class="font-semibold text-slate-800">₹{{ number_format($sgst, 2) }}</span>
                    </div>
                @else
                    <div class="flex justify-between text-slate-600">
                        <span>IGST (Integrated Tax):</span>
                        <span class="font-semibold text-slate-800">₹{{ number_format($igst, 2) }}</span>
                    </div>
                @endif

                <div class="flex justify-between text-slate-600">
                    <span>Shipping Charges:</span>
                    <span class="font-semibold text-slate-800">₹{{ number_format($order->shipping_charge, 2) }}</span>
                </div>

                <div class="border-t-2 border-slate-300 pt-2 flex justify-between text-base font-black text-slate-900">
                    <span>Invoice Total:</span>
                    <span class="text-teal-900 text-lg">₹{{ number_format($totalAmount, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Amount in Words -->
        <div class="p-3 bg-slate-50 rounded-lg border border-slate-200 text-xs">
            <strong class="text-slate-700 uppercase tracking-wider text-[10px]">Invoice Amount in Words:</strong>
            <div class="font-semibold text-slate-900 mt-0.5">{{ $amountInWords }}</div>
        </div>

        <!-- Signatory & Legal Terms -->
        <div class="pt-4 border-t border-slate-200 flex flex-col sm:flex-row justify-between items-end gap-6 text-xs text-slate-500">
            <div>
                <p class="font-bold text-slate-700 uppercase tracking-wider text-[10px]">Terms & Declaration:</p>
                <ul class="list-disc list-inside text-[11px] space-y-0.5 mt-1 text-slate-600">
                    <li>Certified genuine Ayurvedic healthcare formulation.</li>
                    <li>Goods once sold can be returned within 7 days in original sealed packaging.</li>
                    <li>All disputes subject to Gurugram / Haryana jurisdiction.</li>
                    <li>This is a computer-generated tax invoice and requires no physical signature.</li>
                </ul>
            </div>
            <div class="text-center sm:text-right flex-shrink-0">
                <div class="text-slate-800 font-bold mb-8">For {{ \App\Models\Setting::get('company_name', 'MantraHeal') }}</div>
                <div class="border-t border-slate-400 pt-1 text-slate-700 text-[11px] font-semibold">Authorized Signatory</div>
            </div>
        </div>

    </div>
</body>
</html>
