<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SHIPPING LABEL - {{ $shipment->tracking_number }} - MantraHeal</title>
    <link rel="stylesheet" href="{{ asset('css/tailwind.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/mantraheal.css') }}">
    <style>
        @media print {
            @page {
                size: 100mm 150mm; /* Standard 4x6 inch shipping label */
                margin: 0;
            }
            .no-print {
                display: none !important;
            }
            body {
                background: #fff !important;
                padding: 0 !important;
                margin: 0 !important;
                font-size: 9pt !important;
                color: #000 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .label-container {
                border: 2px solid #000 !important;
                box-shadow: none !important;
                width: 100mm !important;
                max-width: 100mm !important;
                height: 148mm !important;
                margin: 0 auto !important;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body class="bg-slate-100 p-4 sm:p-8 font-sans antialiased text-slate-900">

    <!-- Action Bar -->
    <div class="max-w-md mx-auto mb-4 flex items-center justify-between no-print">
        <a href="{{ route('deliveries.index') }}" class="btn btn-secondary text-xs inline-flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Deliveries
        </a>
        <button onclick="window.print()" class="btn bg-indigo-700 hover:bg-indigo-600 text-white font-bold text-xs px-4 py-2 rounded-lg shadow-sm flex items-center gap-1.5 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Print Shipping Label (4x6")
        </button>
    </div>

    <!-- 4x6 Shipping Label Container -->
    <div class="max-w-md mx-auto bg-white border-2 border-slate-900 p-4 rounded-xl shadow-lg label-container space-y-3">
        
        <!-- Header: Courier & Service Type -->
        <div class="flex items-center justify-between border-b-2 border-slate-900 pb-2">
            <div>
                <div class="text-xl font-black uppercase tracking-tight text-slate-900">{{ $shipment->courier_name }}</div>
                <div class="text-[10px] font-bold text-slate-600 uppercase tracking-widest">Express Surface / Air Cargo</div>
            </div>
            <div class="text-right">
                <span class="border-2 border-slate-900 px-2 py-0.5 font-mono text-sm font-black uppercase">
                    {{ substr($shipment->order?->shipping_pincode ?? '110001', 0, 3) }}-HUB
                </span>
            </div>
        </div>

        <!-- Simulated Barcode & AWB Code -->
        <div class="text-center py-1 border-b-2 border-slate-900 space-y-1">
            <!-- Pure CSS Barcode Representation -->
            <div class="flex justify-center items-end h-12 gap-0.5 overflow-hidden">
                @for($i = 0; $i < 48; $i++)
                    @php $w = ($i % 3 === 0) ? 'w-1' : (($i % 2 === 0) ? 'w-1.5' : 'w-0.5'); @endphp
                    <div class="{{ $w }} bg-black h-full"></div>
                @endfor
            </div>
            <div class="font-mono text-base font-black tracking-widest uppercase">
                AWB: {{ $shipment->tracking_number }}
            </div>
        </div>

        <!-- COD Collection or Prepaid Banner -->
        <div class="border-2 border-slate-900 p-2 text-center rounded">
            @if(strtoupper($shipment->order?->payment_method ?? '') === 'COD')
                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-700">Cash on Delivery (COD)</div>
                <div class="text-xl font-black text-slate-950">COLLECT CASH: ₹{{ number_format($shipment->order?->grand_total, 2) }}</div>
            @else
                <div class="text-sm font-black text-slate-950 uppercase tracking-wider">
                    ✓ PREPAID ORDER — DO NOT COLLECT CASH
                </div>
            @endif
        </div>

        <!-- Shipping Destination (SHIP TO) -->
        <div class="border-b-2 border-slate-900 pb-2 text-xs space-y-1">
            <div class="font-bold text-slate-600 uppercase tracking-wider text-[10px]">SHIP TO:</div>
            <div class="text-sm font-black text-slate-950">{{ $shipment->order?->shipping_name ?? $shipment->order?->customer?->name }}</div>
            <div class="text-slate-800 leading-tight">
                {{ $shipment->order?->shipping_address_line1 }}
                @if($shipment->order?->shipping_address_line2) , {{ $shipment->order?->shipping_address_line2 }} @endif
            </div>
            <div class="text-slate-800 font-bold">
                {{ $shipment->order?->shipping_city }}, {{ $shipment->order?->shipping_state }}
            </div>
            <div class="flex items-center justify-between pt-1">
                <div class="text-xl font-black font-mono tracking-wider">
                    PIN: {{ $shipment->order?->shipping_pincode }}
                </div>
                <div class="text-xs font-mono font-bold">
                    TEL: {{ $shipment->order?->shipping_phone ?? $shipment->order?->customer?->phone }}
                </div>
            </div>
        </div>

        <!-- Order Items Summary -->
        <div class="border-b-2 border-slate-900 pb-2 text-[11px]">
            <div class="flex justify-between font-bold text-slate-600 uppercase text-[9px] mb-1">
                <span>Order #{{ $shipment->order?->order_number }}</span>
                <span>Date: {{ $shipment->order?->order_date->format('d/m/Y') }}</span>
            </div>
            <div class="truncate text-slate-800 font-medium">
                @foreach($shipment->order?->items ?? [] as $it)
                    {{ $it->product_name }} (x{{ $it->quantity }}){{ !$loop->last ? ', ' : '' }}
                @endforeach
            </div>
        </div>

        <!-- Sender Return Address (SHIP FROM) -->
        <div class="text-[10px] text-slate-600 space-y-0.5">
            <div class="font-bold uppercase tracking-wider text-slate-800">RETURN / SHIP FROM:</div>
            <div class="font-bold text-slate-900">{{ \App\Models\Setting::get('company_name', 'MantraHeal E-Commerce Center') }}</div>
            <div>{{ \App\Models\Setting::get('company_address', 'Plot No. 42, Sector 18, Udyog Vihar, Gurugram, Haryana - 122015') }}</div>
            <div>Support: {{ \App\Models\Setting::get('support_phone', '+91 98765 43210') }} | GSTIN: {{ \App\Models\Setting::get('gstin', '06AABCM1234F1Z8') }}</div>
        </div>

    </div>
</body>
</html>
