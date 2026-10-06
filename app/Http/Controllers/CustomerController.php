<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\User;
use App\Services\CustomerService;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    public function __construct(
        protected CustomerService $customerService,
        protected ReportService $reportService
    ) {}

    public function index(Request $request)
    {
        $query = Customer::with(['assignedUser', 'defaultAddress']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('customer_code', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($source = $request->input('source')) {
            $query->where('customer_source', $source);
        }

        if ($type = $request->input('type')) {
            $query->where('customer_type', $type);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($assignedTo = $request->input('assigned_user_id')) {
            $query->where('assigned_user_id', $assignedTo);
        }

        if ($state = $request->input('state')) {
            $query->whereHas('addresses', function ($q) use ($state) {
                $q->where('state', $state);
            });
        }

        $customers = $query->latest()->paginate(15)->withQueryString();
        $salesExecutives = User::whereIn('role_slug', ['sales_executive', 'sales_manager'])->get();

        return view('customers.index', compact('customers', 'salesExecutives'));
    }

    public function create()
    {
        $salesExecutives = User::whereIn('role_slug', ['sales_executive', 'sales_manager'])->get();
        return view('customers.create', compact('salesExecutives'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'customer_source' => 'required|string',
            'customer_type' => 'required|string',
            'assigned_user_id' => 'nullable|exists:users,id',
            'tags' => 'nullable|string',
            'notes' => 'nullable|string',
            'address_line1' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
        ]);

        $customer = DB::transaction(function () use ($validated) {
            $customerCode = 'MH-CUST-' . strtoupper(Str::random(6));

            $customer = Customer::create([
                'customer_code' => $customerCode,
                'name' => $validated['name'],
                'mobile' => $validated['mobile'],
                'whatsapp' => $validated['whatsapp'] ?? $validated['mobile'],
                'email' => $validated['email'] ?? null,
                'customer_source' => $validated['customer_source'],
                'customer_type' => $validated['customer_type'],
                'assigned_user_id' => $validated['assigned_user_id'] ?? null,
                'tags' => $validated['tags'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            if (!empty($validated['address_line1'])) {
                CustomerAddress::create([
                    'customer_id' => $customer->id,
                    'type' => 'shipping',
                    'address_line1' => $validated['address_line1'],
                    'city' => $validated['city'] ?? 'New Delhi',
                    'state' => $validated['state'] ?? 'Delhi',
                    'pincode' => $validated['pincode'] ?? '110001',
                    'is_default' => true,
                ]);
            }

            AuditLog::log('created', $customer, "Customer {$customer->name} created ({$customer->customer_code})");

            return $customer;
        });

        return redirect()->route('customers.show', $customer->id)
            ->with('success', 'Customer profile created successfully.');
    }

    public function show(Customer $customer)
    {
        $customer->load([
            'addresses',
            'assignedUser',
            'orders.items',
            'salesCalls.recording',
            'salesCalls.user',
            'callRecordings.user',
            'followups.user',
            'returns',
            'tickets.messages',
            'communicationLogs',
        ]);

        $timeline = $this->customerService->getCustomerTimeline($customer);
        $salesExecutives = User::whereIn('role_slug', ['sales_executive', 'sales_manager'])->get();

        return view('customers.show', compact('customer', 'timeline', 'salesExecutives'));
    }

    public function edit(Customer $customer)
    {
        $customer->load('defaultAddress');
        $salesExecutives = User::whereIn('role_slug', ['sales_executive', 'sales_manager'])->get();
        return view('customers.edit', compact('customer', 'salesExecutives'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:20',
            'whatsapp' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'customer_source' => 'required|string',
            'customer_type' => 'required|string',
            'assigned_user_id' => 'nullable|exists:users,id',
            'status' => 'required|string',
            'tags' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $oldValues = $customer->toArray();
        $customer->update($validated);

        AuditLog::log('updated', $customer, "Customer {$customer->name} updated", $oldValues, $customer->toArray());

        return redirect()->route('customers.show', $customer->id)
            ->with('success', 'Customer details updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        AuditLog::log('deleted', $customer, "Customer {$customer->name} deleted");
        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }

    public function exportCsv()
    {
        $customers = Customer::with('assignedUser')->get();
        $headers = ['Customer Code', 'Name', 'Mobile', 'Email', 'Source', 'Type', 'Total Orders', 'Total Spend (INR)', 'AOV (INR)', 'Assigned To', 'Status', 'Created At'];
        $rows = [];

        foreach ($customers as $c) {
            $rows[] = [
                $c->customer_code,
                $c->name,
                $c->mobile,
                $c->email ?? '',
                $c->customer_source,
                $c->customer_type,
                $c->total_orders,
                $c->total_spend,
                $c->average_order_value,
                $c->assignedUser?->name ?? 'Unassigned',
                $c->status,
                $c->created_at->format('Y-m-d H:i'),
            ];
        }

        return $this->reportService->exportCsv($headers, $rows, 'mantraheal_customers_' . date('Y-m-d') . '.csv');
    }
}
