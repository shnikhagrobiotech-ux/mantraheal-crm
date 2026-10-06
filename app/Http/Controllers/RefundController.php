<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\OrderReturn;
use App\Models\Refund;
use App\Services\ReturnRefundService;
use Illuminate\Http\Request;

class RefundController extends Controller
{
    public function __construct(
        protected ReturnRefundService $returnRefundService
    ) {}

    public function index(Request $request)
    {
        $refunds = Refund::with(['order', 'orderReturn', 'customer', 'processor'])
            ->latest('processed_at')
            ->paginate(15);

        return view('refunds.index', compact('refunds'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_return_id' => 'required|exists:order_returns,id',
            'amount' => 'required|numeric|min:0.01',
            'refund_method' => 'required|string',
            'transaction_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $return = OrderReturn::findOrFail($validated['order_return_id']);
        $refund = $this->returnRefundService->processRefund($return, $validated);

        return back()->with('success', "Refund ₹{$refund->amount} processed successfully.");
    }
}
