<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Leads & CRM Pipeline
            ['name' => 'View Leads', 'slug' => 'leads.view', 'module' => 'leads', 'description' => 'View leads list and pipeline kanban'],
            ['name' => 'Create Leads', 'slug' => 'leads.create', 'module' => 'leads', 'description' => 'Add new leads to the pipeline'],
            ['name' => 'Edit Leads', 'slug' => 'leads.edit', 'module' => 'leads', 'description' => 'Update lead details, stage, and notes'],
            ['name' => 'Delete Leads', 'slug' => 'leads.delete', 'module' => 'leads', 'description' => 'Delete lead records'],
            ['name' => 'Import Leads', 'slug' => 'leads.import', 'module' => 'leads', 'description' => 'Upload CSV files to batch import leads'],
            ['name' => 'Export Leads', 'slug' => 'leads.export', 'module' => 'leads', 'description' => 'Export leads data to CSV format'],
            ['name' => 'Convert Leads', 'slug' => 'leads.convert', 'module' => 'leads', 'description' => 'Convert leads into customers and orders'],

            // Customers 360
            ['name' => 'View Customers', 'slug' => 'customers.view', 'module' => 'customers', 'description' => 'View customer directory and 360 history'],
            ['name' => 'Create Customers', 'slug' => 'customers.create', 'module' => 'customers', 'description' => 'Create new customer profiles'],
            ['name' => 'Edit Customers', 'slug' => 'customers.edit', 'module' => 'customers', 'description' => 'Update customer information & addresses'],
            ['name' => 'Delete Customers', 'slug' => 'customers.delete', 'module' => 'customers', 'description' => 'Delete customer records'],
            ['name' => 'Export Customers', 'slug' => 'customers.export', 'module' => 'customers', 'description' => 'Export customer list to CSV'],

            // Telesales & Calls
            ['name' => 'View Calls', 'slug' => 'calls.view', 'module' => 'calls', 'description' => 'View outbound and inbound call logs'],
            ['name' => 'Make Calls', 'slug' => 'calls.create', 'module' => 'calls', 'description' => 'Initiate calls and log call outcomes'],
            ['name' => 'Listen Call Recordings', 'slug' => 'recordings.listen', 'module' => 'calls', 'description' => 'Listen to audio call recordings for QA'],
            ['name' => 'Use Softphone Dialer', 'slug' => 'dialer.use', 'module' => 'calls', 'description' => 'Access in-app web dialer interface'],

            // Follow-ups
            ['name' => 'Manage Follow-ups', 'slug' => 'followups.manage', 'module' => 'followups', 'description' => 'Create, complete, and reschedule follow-ups'],

            // Orders & Invoicing
            ['name' => 'View Orders', 'slug' => 'orders.view', 'module' => 'orders', 'description' => 'View customer orders and status'],
            ['name' => 'Create Orders', 'slug' => 'orders.create', 'module' => 'orders', 'description' => 'Create new manual or telesales orders'],
            ['name' => 'Edit Orders', 'slug' => 'orders.edit', 'module' => 'orders', 'description' => 'Update order items and delivery status'],
            ['name' => 'Delete Orders', 'slug' => 'orders.delete', 'module' => 'orders', 'description' => 'Cancel or delete orders'],
            ['name' => 'View Tax Invoices', 'slug' => 'orders.invoice', 'module' => 'orders', 'description' => 'Generate and print GST tax invoices'],
            ['name' => 'Record Payments', 'slug' => 'orders.payment', 'module' => 'orders', 'description' => 'Record payments and collection receipts'],
            ['name' => 'Export Orders', 'slug' => 'orders.export', 'module' => 'orders', 'description' => 'Export orders report to CSV'],

            // Products & Catalog
            ['name' => 'View Products', 'slug' => 'products.view', 'module' => 'inventory', 'description' => 'View Ayurvedic catalog products and pricing'],
            ['name' => 'Manage Products', 'slug' => 'products.manage', 'module' => 'inventory', 'description' => 'Create, edit, or remove catalog products & variants'],
            ['name' => 'Manage Categories', 'slug' => 'categories.manage', 'module' => 'inventory', 'description' => 'Organize product categories and taxonomy'],

            // Inventory & Warehouses
            ['name' => 'View Inventory', 'slug' => 'inventory.view', 'module' => 'inventory', 'description' => 'View stock balances and inventory availability'],
            ['name' => 'View Stock Ledger', 'slug' => 'inventory.ledger', 'module' => 'inventory', 'description' => 'View transactional stock movement ledger'],
            ['name' => 'Adjust Inventory', 'slug' => 'inventory.adjust', 'module' => 'inventory', 'description' => 'Perform manual inventory corrections and audits'],
            ['name' => 'Manage Warehouses', 'slug' => 'warehouses.manage', 'module' => 'inventory', 'description' => 'Manage multi-location warehouse hubs'],
            ['name' => 'Manage Batches & FEFO', 'slug' => 'batches.manage', 'module' => 'inventory', 'description' => 'Track batch numbers, manufacturing & expiry dates'],

            // Procurement & GRN
            ['name' => 'Manage Suppliers', 'slug' => 'suppliers.manage', 'module' => 'purchase', 'description' => 'Maintain vendor & raw material supplier list'],
            ['name' => 'Purchase Orders', 'slug' => 'purchase_orders.manage', 'module' => 'purchase', 'description' => 'Create and approve supplier purchase orders'],
            ['name' => 'Goods Receipt (GRN)', 'slug' => 'grn.manage', 'module' => 'purchase', 'description' => 'Record inward stock shipments with batch verification'],

            // Logistics & Dispatch
            ['name' => 'Manage Deliveries', 'slug' => 'deliveries.manage', 'module' => 'deliveries', 'description' => 'Book Shiprocket courier AWBs and generate shipping labels'],

            // Returns, QC & Refunds
            ['name' => 'Manage Returns & QC', 'slug' => 'returns.manage', 'module' => 'returns', 'description' => 'Inspect returned items and update QC status'],
            ['name' => 'Process Refunds', 'slug' => 'refunds.manage', 'module' => 'returns', 'description' => 'Issue Razorpay/Bank refunds to customers'],
            ['name' => 'Manage RTO', 'slug' => 'rto.manage', 'module' => 'returns', 'description' => 'Track return to origin parcels and re-attempt delivery'],

            // Customer Support
            ['name' => 'Manage Support Tickets', 'slug' => 'support.manage', 'module' => 'support', 'description' => 'Respond to customer complaints and resolve tickets'],

            // Communications
            ['name' => 'WhatsApp Messaging', 'slug' => 'whatsapp.manage', 'module' => 'communication', 'description' => 'Send WhatsApp templates and customer notifications'],

            // Marketing & Integrations
            ['name' => 'Marketing Campaigns', 'slug' => 'marketing.view', 'module' => 'marketing', 'description' => 'Analyze campaign attribution and UTM channels'],
            ['name' => 'Shopify Sync', 'slug' => 'shopify.sync', 'module' => 'marketing', 'description' => 'Synchronize orders, fulfillment, and inventory with Shopify'],
            ['name' => 'Meta Ads Sync', 'slug' => 'meta.sync', 'module' => 'marketing', 'description' => 'Ingest and configure Facebook / Instagram lead forms'],

            // Reports
            ['name' => 'View Reports', 'slug' => 'reports.view', 'module' => 'reports', 'description' => 'Access sales, financial, call, and inventory analytics'],

            // Administration
            ['name' => 'Manage Employees & Roles', 'slug' => 'employees.manage', 'module' => 'administration', 'description' => 'Create staff logins, modify permissions, assign designations'],
            ['name' => 'View Audit Logs', 'slug' => 'audit.view', 'module' => 'administration', 'description' => 'Inspect compliance activity logs and event trails'],
            ['name' => 'System Settings', 'slug' => 'settings.manage', 'module' => 'administration', 'description' => 'Configure company details, GST rates, and API credentials'],
        ];

        $permissionModels = [];
        foreach ($permissions as $p) {
            $permissionModels[$p['slug']] = Permission::updateOrCreate(
                ['slug' => $p['slug']],
                [
                    'name' => $p['name'],
                    'module' => $p['module'],
                    'description' => $p['description'],
                ]
            );
        }

        $allPermissionIds = collect($permissionModels)->pluck('id')->toArray();

        // Ensure all 7 standard roles exist
        $rolesData = [
            'super_admin' => ['name' => 'Super Admin', 'description' => 'Unrestricted root administrator with total system control'],
            'admin' => ['name' => 'Admin', 'description' => 'Business operations administrator with full functional access'],
            'sales_manager' => ['name' => 'Sales Manager', 'description' => 'Manager oversee leads, customer relationships, sales calls and rep quotas'],
            'sales_executive' => ['name' => 'Sales Executive', 'description' => 'Front-line rep managing assigned leads, telephonic calls and orders'],
            'inventory_manager' => ['name' => 'Inventory Manager', 'description' => 'Controls stock ledger, batches, FEFO expiry and warehouse receipts'],
            'customer_support' => ['name' => 'Customer Support', 'description' => 'Handles customer tickets, complaints, returns and order inquiries'],
            'accounts' => ['name' => 'Accounts', 'description' => 'Oversees payments, refunds, invoices and billing reconciliation'],
        ];

        $roles = [];
        foreach ($rolesData as $slug => $data) {
            $roles[$slug] = Role::firstOrCreate(
                ['slug' => $slug],
                ['name' => $data['name'], 'description' => $data['description']]
            );
        }

        // Map roles to permissions
        $rolePermissionMap = [
            'super_admin' => $allPermissionIds,
            'admin' => $allPermissionIds,

            'sales_manager' => [
                'leads.view', 'leads.create', 'leads.edit', 'leads.delete', 'leads.import', 'leads.export', 'leads.convert',
                'customers.view', 'customers.create', 'customers.edit', 'customers.export',
                'calls.view', 'calls.create', 'recordings.listen', 'dialer.use',
                'followups.manage',
                'orders.view', 'orders.create', 'orders.edit', 'orders.invoice', 'orders.export',
                'products.view',
                'whatsapp.manage',
                'marketing.view', 'shopify.sync', 'meta.sync',
                'reports.view',
            ],

            'sales_executive' => [
                'leads.view', 'leads.create', 'leads.edit', 'leads.convert',
                'customers.view', 'customers.create', 'customers.edit',
                'calls.view', 'calls.create', 'dialer.use',
                'followups.manage',
                'orders.view', 'orders.create', 'orders.invoice',
                'products.view',
            ],

            'inventory_manager' => [
                'products.view', 'products.manage', 'categories.manage',
                'inventory.view', 'inventory.ledger', 'inventory.adjust', 'warehouses.manage', 'batches.manage',
                'suppliers.manage', 'purchase_orders.manage', 'grn.manage',
                'deliveries.manage',
                'returns.manage', 'rto.manage',
                'reports.view',
            ],

            'customer_support' => [
                'customers.view', 'customers.create', 'customers.edit',
                'orders.view', 'orders.invoice',
                'calls.view', 'calls.create', 'dialer.use',
                'support.manage',
                'returns.manage', 'refunds.manage', 'rto.manage',
                'whatsapp.manage',
            ],

            'accounts' => [
                'orders.view', 'orders.invoice', 'orders.payment', 'orders.export',
                'refunds.manage',
                'reports.view',
                'suppliers.manage', 'purchase_orders.manage',
                'products.view',
            ],
        ];

        foreach ($rolePermissionMap as $roleSlug => $slugs) {
            if (!isset($roles[$roleSlug])) {
                continue;
            }

            if ($roleSlug === 'super_admin' || $roleSlug === 'admin') {
                $roles[$roleSlug]->permissions()->sync($allPermissionIds);
            } else {
                $ids = [];
                foreach ($slugs as $slug) {
                    if (isset($permissionModels[$slug])) {
                        $ids[] = $permissionModels[$slug]->id;
                    }
                }
                $roles[$roleSlug]->permissions()->sync($ids);
            }
        }
    }
}
