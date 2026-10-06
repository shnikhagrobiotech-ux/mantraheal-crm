<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Batch;
use App\Models\CallRecording;
use App\Models\Category;
use App\Models\CommunicationLog;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\CustomerTag;
use App\Models\FollowUp;
use App\Models\GoodsReceivedNote;
use App\Models\GrnItem;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\MarketingCampaign;
use App\Models\MarketingSource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderReturn;
use App\Models\ReturnItem;
use App\Models\Payment;
use App\Models\Permission;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Refund;
use App\Models\Role;
use App\Models\RtoRecord;
use App\Models\SalesCall;
use App\Models\Setting;
use App\Models\Shipment;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\SupportTicket;
use App\Models\TicketMessage;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WhatsAppTemplate;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. ROLES & PERMISSIONS
        $rolesData = [
            ['name' => 'Super Admin', 'slug' => 'super_admin', 'description' => 'Unrestricted root administrator with total system control'],
            ['name' => 'Admin', 'slug' => 'admin', 'description' => 'Business operations administrator with full functional access'],
            ['name' => 'Sales Manager', 'slug' => 'sales_manager', 'description' => 'Manager oversee leads, customer relationships, sales calls and rep quotas'],
            ['name' => 'Sales Executive', 'slug' => 'sales_executive', 'description' => 'Front-line rep managing assigned leads, telephonic calls and orders'],
            ['name' => 'Inventory Manager', 'slug' => 'inventory_manager', 'description' => 'Controls stock ledger, batches, FEFO expiry and warehouse receipts'],
            ['name' => 'Customer Support', 'slug' => 'customer_support', 'description' => 'Handles customer tickets, complaints, returns and order inquiries'],
            ['name' => 'Accounts', 'slug' => 'accounts', 'description' => 'Oversees payments, refunds, invoices and billing reconciliation'],
        ];

        $roles = [];
        foreach ($rolesData as $rd) {
            $roles[$rd['slug']] = Role::create($rd);
        }

        // 2. USERS (STAFF)
        $password = Hash::make('password123');
        $usersData = [
            [
                'name' => 'Aarav Mehta',
                'email' => 'admin@mantraheal.com',
                'phone' => '+91 98100 11001',
                'role_slug' => 'super_admin',
                'role_id' => $roles['super_admin']->id,
                'designation' => 'Chief Executive & Founder',
            ],
            [
                'name' => 'Sunita Sen',
                'email' => 'operations@mantraheal.com',
                'phone' => '+91 98100 11002',
                'role_slug' => 'admin',
                'role_id' => $roles['admin']->id,
                'designation' => 'Operations Director',
            ],
            [
                'name' => 'Vikram Rathore',
                'email' => 'vikram.manager@mantraheal.com',
                'phone' => '+91 98100 11003',
                'role_slug' => 'sales_manager',
                'role_id' => $roles['sales_manager']->id,
                'designation' => 'VP of Telesales & Growth',
            ],
            [
                'name' => 'Rahul Sharma',
                'email' => 'rahul.sales@mantraheal.com',
                'phone' => '+91 98100 11004',
                'role_slug' => 'sales_executive',
                'role_id' => $roles['sales_executive']->id,
                'designation' => 'Senior Wellness Consultant',
            ],
            [
                'name' => 'Priya Nair',
                'email' => 'priya.sales@mantraheal.com',
                'phone' => '+91 98100 11005',
                'role_slug' => 'sales_executive',
                'role_id' => $roles['sales_executive']->id,
                'designation' => 'Ayurvedic Sales Executive',
            ],
            [
                'name' => 'Harish Verma',
                'email' => 'stock@mantraheal.com',
                'phone' => '+91 98100 11006',
                'role_slug' => 'inventory_manager',
                'role_id' => $roles['inventory_manager']->id,
                'designation' => 'Chief Warehouse Controller',
            ],
            [
                'name' => 'Ananya Joshi',
                'email' => 'support@mantraheal.com',
                'phone' => '+91 98100 11007',
                'role_slug' => 'customer_support',
                'role_id' => $roles['customer_support']->id,
                'designation' => 'Customer Care Lead',
            ],
            [
                'name' => 'Deepak Gupta',
                'email' => 'accounts@mantraheal.com',
                'phone' => '+91 98100 11008',
                'role_slug' => 'accounts',
                'role_id' => $roles['accounts']->id,
                'designation' => 'Finance & Reconciliation Manager',
            ],
        ];

        $users = [];
        foreach ($usersData as $ud) {
            $ud['password'] = $password;
            $ud['status'] = 'active';
            $users[$ud['email']] = User::create($ud);
        }

        // 3. SETTINGS
        $defaultSettings = [
            'company_name' => 'MantraHeal',
            'app_name' => 'MantraHeal CRM',
            'support_email' => 'support@mantraheal.com',
            'support_phone' => '+91 98765 43210',
            'company_address' => 'Plot No. 42, Sector 18, Udyog Vihar, Gurugram, Haryana, 122015, India',
            'gstin' => '06AABCM1234F1Z8',
            'pan_number' => 'AABCM1234F',
            'currency' => 'INR (₹)',
            'default_gst' => '12',
            'shopify_shop_url' => 'mantraheal.myshopify.com',
            'shiprocket_email' => 'logistics@mantraheal.com',
            'whatsapp_phone_number_id' => '109283746501928',
            'whatsapp_waba_id' => '981273645019283',
        ];
        foreach ($defaultSettings as $sk => $sv) {
            Setting::set($sk, $sv);
        }

        // 4. WAREHOUSES
        $warehousesData = [
            [
                'name' => 'Central Depot (Gurugram)',
                'code' => 'WH-GURUGRAM-CENTRAL',
                'contact_person' => 'Harish Verma',
                'phone' => '+91 98100 11006',
                'email' => 'wh.gurugram@mantraheal.com',
                'address' => 'Plot 42, Sector 18 Industrial Area',
                'city' => 'Gurugram',
                'state' => 'Haryana',
                'pincode' => '122015',
                'is_active' => true,
                'is_default' => true,
            ],
            [
                'name' => 'Delhi Okhla Fulfillment Depot',
                'code' => 'WH-DELHI-OKHLA',
                'contact_person' => 'Mohit Taneja',
                'phone' => '+91 98100 22001',
                'email' => 'wh.delhi@mantraheal.com',
                'address' => 'Phase-III, Okhla Industrial Estate',
                'city' => 'New Delhi',
                'state' => 'Delhi',
                'pincode' => '110020',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'Mumbai Bhiwandi Logistics Hub',
                'code' => 'WH-MUMBAI-BHIWANDI',
                'contact_person' => 'Sanjay Patil',
                'phone' => '+91 98100 33001',
                'email' => 'wh.mumbai@mantraheal.com',
                'address' => 'Gala 14, Arihant Logistics Park, Bhiwandi',
                'city' => 'Mumbai / Thane',
                'state' => 'Maharashtra',
                'pincode' => '421302',
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'Indore Regional Warehouse',
                'code' => 'WH-INDORE-REGIONAL',
                'contact_person' => 'Rajesh Chouhan',
                'phone' => '+91 98100 44001',
                'email' => 'wh.indore@mantraheal.com',
                'address' => 'Sanwer Road Industrial Area, Sector C',
                'city' => 'Indore',
                'state' => 'Madhya Pradesh',
                'pincode' => '452015',
                'is_active' => true,
                'is_default' => false,
            ],
        ];

        $warehouses = [];
        foreach ($warehousesData as $wd) {
            $warehouses[] = Warehouse::create($wd);
        }
        $primaryWh = $warehouses[0];
        $mumbaiWh = $warehouses[2];

        // 5. CATEGORIES
        $categoriesData = [
            ['name' => 'Pure Shilajit & Rasayanas', 'slug' => 'pure-shilajit-rasayanas', 'description' => 'Himalayan wild-harvested resins, fulvic-rich energy tonics and ancient rejuvenators.'],
            ['name' => 'Immunity & Vitality', 'slug' => 'immunity-vitality', 'description' => 'Root extracts and bio-available adaptogens supporting vigor, stamina, and endocrine balance.'],
            ['name' => 'Joint & Muscle Care', 'slug' => 'joint-muscle-care', 'description' => 'Shallaki, Boswellia, and Guggulu formulations for cartilege lubrication and mobility.'],
            ['name' => 'Digestive & Gut Detox', 'slug' => 'digestive-gut-detox', 'description' => 'Amla, Haritaki, and Bibhitaki cold-pressed juices and fiber detoxifiers.'],
            ['name' => 'Ayurvedic Hair & Skin Elixirs', 'slug' => 'ayurvedic-hair-skin', 'description' => 'Cold-pressed herb-infused botanical oils, Kumkumadi and Bhringraj hair roots therapies.'],
        ];

        $categories = [];
        foreach ($categoriesData as $cd) {
            $cd['is_active'] = true;
            $categories[] = Category::create($cd);
        }

        // 6. PRODUCTS (MANTRAHEAL FORMULATIONS - ZERO CORDYGEN REFERENCES)
        $productsData = [
            [
                'name' => 'MantraHeal Pure Himalayan Shilajit Resin (20g)',
                'product_code' => 'MH-SHIL-20G',
                'sku' => 'MH-SHILAJIT-RESIN-20',
                'slug' => 'mantraheal-pure-himalayan-shilajit-resin-20g',
                'category_id' => $categories[0]->id,
                'brand' => 'MantraHeal',
                'description' => 'Authentic Grade-A Himalayan Shilajit harvested at 18,000+ ft. Rich in >75% Fulvic Acid and 84+ ionic trace minerals. Supports cellular ATP and vitality.',
                'mrp' => 1899.00,
                'selling_price' => 1299.00,
                'purchase_price' => 450.00,
                'gst_percent' => 12.00,
                'hsn_code' => '30049011',
                'barcode' => '890601234001',
                'minimum_stock' => 25,
                'reorder_level' => 50,
                'is_active' => true,
            ],
            [
                'name' => 'MantraHeal Ashwagandha KSM-66 Max (60 Capsules)',
                'product_code' => 'MH-ASHW-60C',
                'sku' => 'MH-ASHWAGANDHA-KSM66-60',
                'slug' => 'mantraheal-ashwagandha-ksm-66-max-60-capsules',
                'category_id' => $categories[1]->id,
                'brand' => 'MantraHeal',
                'description' => 'Full-spectrum root extract with highest concentration of withanolides (>5%). Promotes healthy cortisol response, stress resilience, and restorative sleep.',
                'mrp' => 999.00,
                'selling_price' => 749.00,
                'purchase_price' => 240.00,
                'gst_percent' => 12.00,
                'hsn_code' => '30049011',
                'barcode' => '890601234002',
                'minimum_stock' => 30,
                'reorder_level' => 60,
                'is_active' => true,
            ],
            [
                'name' => 'MantraHeal Triphala & Aloe Vera Detox Juice (1000ml)',
                'product_code' => 'MH-TRIP-1L',
                'sku' => 'MH-TRIPHALA-ALOE-1000',
                'slug' => 'mantraheal-triphala-aloe-vera-detox-juice-1000ml',
                'category_id' => $categories[3]->id,
                'brand' => 'MantraHeal',
                'description' => 'Cold-pressed organic Amla, Haritaki, and Baheda blended with pure inner-leaf Aloe Vera. Promotes digestive peristalsis and gentle morning bowel cleanse.',
                'mrp' => 599.00,
                'selling_price' => 449.00,
                'purchase_price' => 140.00,
                'gst_percent' => 12.00,
                'hsn_code' => '30049011',
                'barcode' => '890601234003',
                'minimum_stock' => 20,
                'reorder_level' => 40,
                'is_active' => true,
            ],
            [
                'name' => 'MantraHeal Organic Bhringraj & Rosemary Hair Oil (200ml)',
                'product_code' => 'MH-BHRING-200',
                'sku' => 'MH-BHRINGRAJ-HAIR-200',
                'slug' => 'mantraheal-organic-bhringraj-rosemary-hair-oil-200ml',
                'category_id' => $categories[4]->id,
                'brand' => 'MantraHeal',
                'description' => 'Kshirpak Ayurvedic process infusion of Eclipta Alba (Bhringraj), Rosemary essential oil, and cold-pressed Sesame. Nourishes hair follicles and controls scalp thinning.',
                'mrp' => 799.00,
                'selling_price' => 599.00,
                'purchase_price' => 190.00,
                'gst_percent' => 18.00,
                'hsn_code' => '33059011',
                'barcode' => '890601234004',
                'minimum_stock' => 20,
                'reorder_level' => 45,
                'is_active' => true,
            ],
            [
                'name' => 'MantraHeal Shallaki & Boswellia Joint Relief (60 Tablets)',
                'product_code' => 'MH-SHAL-60T',
                'sku' => 'MH-SHALLAKI-BOSWELLIA-60',
                'slug' => 'mantraheal-shallaki-boswellia-joint-relief-60-tablets',
                'category_id' => $categories[2]->id,
                'brand' => 'MantraHeal',
                'description' => 'Standardized 65% Boswellic acids combined with Nirgundi extract. Supports cartilage comfort, knee flexibility, and reduces inflammatory joint stiffness.',
                'mrp' => 849.00,
                'selling_price' => 649.00,
                'purchase_price' => 210.00,
                'gst_percent' => 12.00,
                'hsn_code' => '30049011',
                'barcode' => '890601234005',
                'minimum_stock' => 15,
                'reorder_level' => 35,
                'is_active' => true,
            ],
            [
                'name' => 'MantraHeal Brahmi & Shankhpushpi Memory Syrup (300ml)',
                'product_code' => 'MH-BRAH-300',
                'sku' => 'MH-BRAHMI-MEMORY-300',
                'slug' => 'mantraheal-brahmi-shankhpushpi-memory-syrup-300ml',
                'category_id' => $categories[1]->id,
                'brand' => 'MantraHeal',
                'description' => 'Traditional Medhya Rasayana with Bacopa Monnieri and Convolvulus Pluricaulis in natural honey base. Enhances mental clarity, focus, and cognitive calm.',
                'mrp' => 499.00,
                'selling_price' => 379.00,
                'purchase_price' => 110.00,
                'gst_percent' => 12.00,
                'hsn_code' => '30049011',
                'barcode' => '890601234006',
                'minimum_stock' => 20,
                'reorder_level' => 30,
                'is_active' => true,
            ],
        ];

        $stockQuantities = [180, 240, 120, 150, 95, 14];

        $products = [];
        foreach ($productsData as $pd) {
            $products[] = Product::create($pd);
        }

        // 7. BATCHES & FEFO TRACKING
        $batches = [];
        // Product 0: Shilajit - Normal healthy batch
        $batches[] = Batch::create([
            'batch_number' => 'MH-SHIL-B2601',
            'product_id' => $products[0]->id,
            'warehouse_id' => $primaryWh->id,
            'manufacturing_date' => Carbon::now()->subMonths(3),
            'expiry_date' => Carbon::now()->addMonths(21),
            'cost_price' => 450.00,
            'selling_price' => 1299.00,
            'initial_quantity' => 100,
            'current_quantity' => 90,
        ]);
        // Product 0: Shilajit - Mumbai depot batch
        $batches[] = Batch::create([
            'batch_number' => 'MH-SHIL-B2602',
            'product_id' => $products[0]->id,
            'warehouse_id' => $mumbaiWh->id,
            'manufacturing_date' => Carbon::now()->subMonths(2),
            'expiry_date' => Carbon::now()->addMonths(22),
            'cost_price' => 450.00,
            'selling_price' => 1299.00,
            'initial_quantity' => 100,
            'current_quantity' => 90,
        ]);
        // Product 1: Ashwagandha - Near expiry (24 days left!) for FEFO warning alert
        $batches[] = Batch::create([
            'batch_number' => 'MH-ASHW-B2409',
            'product_id' => $products[1]->id,
            'warehouse_id' => $primaryWh->id,
            'manufacturing_date' => Carbon::now()->subMonths(23),
            'expiry_date' => Carbon::now()->addDays(24),
            'cost_price' => 240.00,
            'selling_price' => 749.00,
            'initial_quantity' => 50,
            'current_quantity' => 40,
        ]);
        // Product 1: Ashwagandha - Healthy fresh batch
        $batches[] = Batch::create([
            'batch_number' => 'MH-ASHW-B2603',
            'product_id' => $products[1]->id,
            'warehouse_id' => $primaryWh->id,
            'manufacturing_date' => Carbon::now()->subMonths(1),
            'expiry_date' => Carbon::now()->addMonths(23),
            'cost_price' => 240.00,
            'selling_price' => 749.00,
            'initial_quantity' => 200,
            'current_quantity' => 200,
        ]);
        // Product 2: Triphala - Expiring in 50 days (60 days category)
        $batches[] = Batch::create([
            'batch_number' => 'MH-TRIP-B2502',
            'product_id' => $products[2]->id,
            'warehouse_id' => $primaryWh->id,
            'manufacturing_date' => Carbon::now()->subMonths(10),
            'expiry_date' => Carbon::now()->addDays(50),
            'cost_price' => 140.00,
            'selling_price' => 449.00,
            'initial_quantity' => 150,
            'current_quantity' => 120,
        ]);
        // Product 3: Hair Oil - Healthy
        $batches[] = Batch::create([
            'batch_number' => 'MH-HAIR-B2601',
            'product_id' => $products[3]->id,
            'warehouse_id' => $primaryWh->id,
            'manufacturing_date' => Carbon::now()->subMonths(2),
            'expiry_date' => Carbon::now()->addMonths(34),
            'cost_price' => 190.00,
            'selling_price' => 599.00,
            'initial_quantity' => 160,
            'current_quantity' => 150,
        ]);

        // 8. STOCK BALANCES & STOCK LEDGER
        foreach ($products as $idx => $p) {
            // Allocate to Central
            $qtyCentral = (int) ($stockQuantities[$idx] * 0.7);
            $qtyMumbai = (int) ($stockQuantities[$idx] * 0.3);

            StockBalance::create([
                'product_id' => $p->id,
                'warehouse_id' => $primaryWh->id,
                'quantity' => $qtyCentral,
                'reserved_quantity' => 0,
            ]);

            StockBalance::create([
                'product_id' => $p->id,
                'warehouse_id' => $mumbaiWh->id,
                'quantity' => $qtyMumbai,
                'reserved_quantity' => 0,
            ]);

            // Stock Movement Entry in immutable ledger
            StockMovement::create([
                'movement_code' => 'MOV-OPN-' . strtoupper(Str::random(6)),
                'product_id' => $p->id,
                'warehouse_id' => $primaryWh->id,
                'movement_type' => 'opening',
                'quantity' => $qtyCentral,
                'balance_after' => $qtyCentral,
                'reference_type' => 'OpeningStockBalance',
                'user_id' => $users['stock@mantraheal.com']->id,
                'notes' => "Opening warehouse setup for {$p->name}",
                'created_at' => Carbon::now()->subDays(30),
            ]);
        }

        // 9. SUPPLIERS
        $suppliersData = [
            [
                'supplier_code' => 'SUP-HIMALAYAN',
                'name' => 'Himalayan Organic Herb Cultivators LLP',
                'contact_person' => 'Devendra Singh Negi',
                'phone' => '+91 98970 12345',
                'email' => 'orders@himalayanherbs.in',
                'gstin' => '05AAACH1234G1ZT',
                'address' => 'Valley Farm No. 8, Joshimath High Range',
                'city' => 'Chamoli',
                'state' => 'Uttarakhand',
                'pincode' => '246443',
                'payment_terms' => 'Net 30 Days',
                'status' => 'active',
            ],
            [
                'supplier_code' => 'SUP-GREENROOTS',
                'name' => 'Greenroots Botanical Extracts Pvt Ltd',
                'contact_person' => 'Dr. Meenakshi Sundaram',
                'phone' => '+91 94440 56789',
                'email' => 'sales@greenrootsextracts.com',
                'gstin' => '33AABCG5678M1ZW',
                'address' => 'SIPCOT Industrial Complex, Phase II',
                'city' => 'Hosur',
                'state' => 'Tamil Nadu',
                'pincode' => '635126',
                'payment_terms' => 'Advance 50%, 50% on Delivery',
                'status' => 'active',
            ],
            [
                'supplier_code' => 'SUP-VEDICPACK',
                'name' => 'Vedic Amber Packaging & Glassworks',
                'contact_person' => 'Ketan Shah',
                'phone' => '+91 98250 98765',
                'email' => 'info@vedicamberpack.com',
                'gstin' => '24AABCV9999K1Z4',
                'address' => 'Plot 88, Changodar Industrial Estate',
                'city' => 'Ahmedabad',
                'state' => 'Gujarat',
                'pincode' => '382213',
                'payment_terms' => 'Net 15 Days',
                'status' => 'active',
            ],
        ];

        $suppliers = [];
        foreach ($suppliersData as $sd) {
            $suppliers[] = Supplier::create($sd);
        }

        // 10. PURCHASE ORDER & GRN
        $po = PurchaseOrder::create([
            'po_number' => 'PO-2026-001',
            'supplier_id' => $suppliers[0]->id,
            'warehouse_id' => $primaryWh->id,
            'po_date' => Carbon::now()->subDays(20),
            'expected_delivery_date' => Carbon::now()->subDays(5),
            'subtotal' => 45000.00,
            'tax_amount' => 5400.00,
            'total_amount' => 50400.00,
            'status' => 'Received',
            'created_by' => $users['stock@mantraheal.com']->id,
            'notes' => '100 units of Pure Himalayan Shilajit raw extract resin',
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'product_id' => $products[0]->id,
            'quantity' => 100,
            'received_quantity' => 100,
            'rate' => 450.00,
            'tax_percent' => 12.00,
            'tax_amount' => 5400.00,
            'total_amount' => 50400.00,
        ]);

        $grn = GoodsReceivedNote::create([
            'grn_number' => 'GRN-2026-001',
            'purchase_order_id' => $po->id,
            'supplier_id' => $suppliers[0]->id,
            'warehouse_id' => $primaryWh->id,
            'received_by' => $users['stock@mantraheal.com']->id,
            'grn_date' => Carbon::now()->subDays(5),
            'invoice_number' => 'HIMALAYAN-INV-9812',
            'invoice_date' => Carbon::now()->subDays(7),
            'status' => 'Approved',
            'remarks' => '100% Quality checked. Seal intact and lab COA attached.',
        ]);

        GrnItem::create([
            'goods_received_note_id' => $grn->id,
            'purchase_order_item_id' => $po->items()->first()->id,
            'product_id' => $products[0]->id,
            'received_quantity' => 100,
            'damaged_quantity' => 0,
            'accepted_quantity' => 100,
            'batch_number' => 'MH-SHIL-B2601',
            'manufacturing_date' => Carbon::now()->subMonths(3),
            'expiry_date' => Carbon::now()->addMonths(21),
        ]);

        // 11. MARKETING SOURCES & CAMPAIGNS
        $marketingSourcesData = [
            ['name' => 'Meta Ads', 'code' => 'meta-ads', 'channel_type' => 'Paid Digital'],
            ['name' => 'Google Search', 'code' => 'google-search', 'channel_type' => 'Paid Digital'],
            ['name' => 'Instagram Organic', 'code' => 'instagram-organic', 'channel_type' => 'Organic Social'],
            ['name' => 'WhatsApp Commerce', 'code' => 'whatsapp-commerce', 'channel_type' => 'Messaging'],
            ['name' => 'Website Direct', 'code' => 'website-direct', 'channel_type' => 'Direct Commerce'],
            ['name' => 'Ayurvedic Doctor Referral', 'code' => 'doctor-referral', 'channel_type' => 'Offline'],
        ];

        $marketingSources = [];
        foreach ($marketingSourcesData as $msd) {
            $msd['is_active'] = true;
            $marketingSources[] = MarketingSource::create($msd);
        }

        MarketingCampaign::create([
            'code' => 'CAMP-VITALITY-26',
            'name' => 'Summer Vitality & Stamina Drive',
            'marketing_source_id' => $marketingSources[0]->id,
            'budget' => 50000.00,
            'start_date' => Carbon::now()->subDays(25),
            'end_date' => Carbon::now()->addDays(15),
            'is_active' => true,
        ]);

        // 12. CUSTOMERS
        $customersData = [
            [
                'customer_code' => 'MH-CUST-1001',
                'name' => 'Dr. Rajesh Deshmukh',
                'email' => 'dr.rajesh.deshmukh@gmail.com',
                'mobile' => '+91 98200 45678',
                'whatsapp' => '+91 98200 45678',
                'customer_type' => 'vip',
                'customer_source' => 'Meta Ads',
                'assigned_user_id' => $users['rahul.sales@mantraheal.com']->id,
                'address_line1' => 'B-402, Sea Green Apartments, Worli Sea Face',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'pincode' => '400018',
                'total_orders' => 4,
                'total_spend' => 5996.00,
                'average_order_value' => 1499.00,
                'status' => 'active',
                'notes' => 'Regular user of MantraHeal Shilajit Resin. Prefers morning delivery.',
            ],
            [
                'customer_code' => 'MH-CUST-1002',
                'name' => 'Sumanth Bhardwaj',
                'email' => 'sumanth.b@outlook.com',
                'mobile' => '+91 98110 56789',
                'whatsapp' => '+91 98110 56789',
                'customer_type' => 'repeat',
                'customer_source' => 'Google Search',
                'assigned_user_id' => $users['rahul.sales@mantraheal.com']->id,
                'address_line1' => 'Flat 204, Block C, Greater Kailash Part 1',
                'city' => 'New Delhi',
                'state' => 'Delhi',
                'pincode' => '110048',
                'total_orders' => 2,
                'total_spend' => 2048.00,
                'average_order_value' => 1024.00,
                'status' => 'active',
                'notes' => 'Consumes Ashwagandha KSM-66 for corporate stress management.',
            ],
            [
                'customer_code' => 'MH-CUST-1003',
                'name' => 'Meera Krishnan',
                'email' => 'meera.krishnan@yahoo.co.in',
                'mobile' => '+91 98450 88990',
                'whatsapp' => '+91 98450 88990',
                'customer_type' => 'new',
                'customer_source' => 'Instagram Organic',
                'assigned_user_id' => $users['priya.sales@mantraheal.com']->id,
                'address_line1' => '12, 4th Cross, Indiranagar Stage 2',
                'city' => 'Bengaluru',
                'state' => 'Karnataka',
                'pincode' => '560038',
                'total_orders' => 1,
                'total_spend' => 599.00,
                'average_order_value' => 599.00,
                'status' => 'active',
                'notes' => 'Ordered Organic Bhringraj Hair Oil. Prompt delivery requested.',
            ],
            [
                'customer_code' => 'MH-CUST-1004',
                'name' => 'Col. Harpreet Singh (Retd.)',
                'email' => 'col.harpreet@rediffmail.com',
                'mobile' => '+91 98720 11223',
                'whatsapp' => '+91 98720 11223',
                'customer_type' => 'repeat',
                'customer_source' => 'Ayurvedic Doctor Referral',
                'assigned_user_id' => $users['priya.sales@mantraheal.com']->id,
                'address_line1' => 'House 52, Sector 8-A',
                'city' => 'Chandigarh',
                'state' => 'Chandigarh',
                'pincode' => '160018',
                'total_orders' => 3,
                'total_spend' => 1947.00,
                'average_order_value' => 649.00,
                'status' => 'active',
                'notes' => 'Takes Shallaki & Boswellia capsules for knee joint cartilage support.',
            ],
            [
                'customer_code' => 'MH-CUST-1005',
                'name' => 'Anil Agarwal',
                'email' => 'anil.agarwal.jaipur@gmail.com',
                'mobile' => '+91 94140 33445',
                'whatsapp' => '+91 94140 33445',
                'customer_type' => 'new',
                'customer_source' => 'Website Direct',
                'assigned_user_id' => $users['rahul.sales@mantraheal.com']->id,
                'address_line1' => 'Plot 108, Civil Lines',
                'city' => 'Jaipur',
                'state' => 'Rajasthan',
                'pincode' => '302006',
                'total_orders' => 1,
                'total_spend' => 1299.00,
                'average_order_value' => 1299.00,
                'status' => 'active',
                'notes' => 'Customer placed COD order via website direct.',
            ],
        ];

        $customers = [];
        foreach ($customersData as $cd) {
            $c = Customer::create($cd);
            CustomerAddress::create([
                'customer_id' => $c->id,
                'type' => 'shipping',
                'address_line1' => $c->address_line1,
                'city' => $c->city,
                'state' => $c->state,
                'pincode' => $c->pincode,
                'is_default' => true,
            ]);
            $customers[] = $c;
        }

        // 13. LEADS & PIPELINE
        $leadsData = [
            [
                'name' => 'Vikram Goel',
                'phone' => '+91 98101 22334',
                'email' => 'v.goel@gurugramcorp.com',
                'source' => 'Meta Ads',
                'stage' => 'Interested',
                'assigned_user_id' => $users['rahul.sales@mantraheal.com']->id,
                'city' => 'Gurugram',
                'state' => 'Haryana',
                'notes' => 'Interested in Shilajit Resin 20g. Discussed pure water solubility and testing certifications.',
            ],
            [
                'name' => 'Pooja Bhatt',
                'phone' => '+91 98205 66778',
                'email' => 'pooja.bhatt92@gmail.com',
                'source' => 'Instagram Organic',
                'stage' => 'Follow-up',
                'assigned_user_id' => $users['priya.sales@mantraheal.com']->id,
                'city' => 'Pune',
                'state' => 'Maharashtra',
                'notes' => 'Requested follow-up call tomorrow at 4 PM regarding hair fall treatment regimen.',
            ],
            [
                'name' => 'Naveen Reddy',
                'phone' => '+91 98480 33445',
                'email' => 'naveen.r@techhyderabad.com',
                'source' => 'Google Search',
                'stage' => 'Order Confirmed',
                'assigned_user_id' => $users['rahul.sales@mantraheal.com']->id,
                'city' => 'Hyderabad',
                'state' => 'Telangana',
                'notes' => 'Confirmed order for Ashwagandha KSM-66 two bottles combo. Converting to customer.',
            ],
            [
                'name' => 'Manish Tiwari',
                'phone' => '+91 99350 44556',
                'email' => 'manish.t@kanpurmail.com',
                'source' => 'Meta Ads',
                'stage' => 'Lost',
                'lost_reason' => 'Price',
                'assigned_user_id' => $users['priya.sales@mantraheal.com']->id,
                'city' => 'Kanpur',
                'state' => 'Uttar Pradesh',
                'notes' => 'Felt premium resin price was above budget; suggested looking for promotional sale.',
            ],
        ];

        $leads = [];
        foreach ($leadsData as $ld) {
            $leads[] = Lead::create($ld);
        }

        // 14. SALES CALLS & CALL RECORDINGS
        // 14. SALES CALLS & CALL RECORDINGS
        // Using storage/app/call-recordings/sample_call.wav which was synthesized earlier!
        $call1 = SalesCall::create([
            'customer_id' => $customers[0]->id,
            'user_id' => $users['rahul.sales@mantraheal.com']->id,
            'direction' => 'outgoing',
            'call_datetime' => Carbon::now()->subDays(2)->setHour(11)->setMinute(15),
            'duration_seconds' => 245,
            'outcome' => 'Order Taken',
            'notes' => 'Discussed repeat purchase of Himalayan Shilajit Resin. Client pleased with stamina results. Confirmed repeat order #MH10291.',
            'follow_up_date' => Carbon::now()->addDays(28),
        ]);

        CallRecording::create([
            'sales_call_id' => $call1->id,
            'customer_id' => $customers[0]->id,
            'user_id' => $users['rahul.sales@mantraheal.com']->id,
            'file_path' => 'call-recordings/sample_call.wav',
            'file_name' => 'call_shilajit_repeat_order.wav',
            'mime_type' => 'audio/wav',
            'duration_seconds' => 245,
            'file_size' => 16044,
        ]);

        $call2 = SalesCall::create([
            'lead_id' => $leads[0]->id,
            'user_id' => $users['rahul.sales@mantraheal.com']->id,
            'direction' => 'outgoing',
            'call_datetime' => Carbon::now()->subDays(1)->setHour(15)->setMinute(30),
            'duration_seconds' => 180,
            'outcome' => 'Interested',
            'notes' => 'Prospect inquired about lab certifications for heavy metals. Shared NABL lab test report PDF via WhatsApp.',
            'follow_up_date' => Carbon::now()->addDays(1),
        ]);

        CallRecording::create([
            'sales_call_id' => $call2->id,
            'customer_id' => null,
            'user_id' => $users['rahul.sales@mantraheal.com']->id,
            'file_path' => 'call-recordings/sample_call.wav',
            'file_name' => 'lead_vikram_consultation.wav',
            'mime_type' => 'audio/wav',
            'duration_seconds' => 180,
            'file_size' => 16044,
        ]);

        // 15. FOLLOW-UPS
        FollowUp::create([
            'customer_id' => $customers[0]->id,
            'user_id' => $users['rahul.sales@mantraheal.com']->id,
            'due_date' => Carbon::today(),
            'due_time' => '11:00:00',
            'reason' => 'Package delivery & dosage advice',
            'priority' => 'High',
            'status' => 'Pending',
            'notes' => 'Follow up with Dr. Rajesh on package delivery confirmation and dosage advice.',
        ]);

        FollowUp::create([
            'lead_id' => $leads[1]->id,
            'user_id' => $users['priya.sales@mantraheal.com']->id,
            'due_date' => Carbon::today(),
            'due_time' => '16:00:00',
            'reason' => 'Bhringraj hair oil application guidance',
            'priority' => 'Medium',
            'status' => 'Pending',
            'notes' => 'Call Pooja Bhatt regarding Bhringraj hair oil application frequency.',
        ]);

        FollowUp::create([
            'lead_id' => $leads[0]->id,
            'user_id' => $users['rahul.sales@mantraheal.com']->id,
            'due_date' => Carbon::yesterday(), // Overdue example
            'due_time' => '14:00:00',
            'reason' => 'Lab testing certification follow-up',
            'priority' => 'High',
            'status' => 'Pending',
            'notes' => 'Check if prospect reviewed NABL lab certificate sent yesterday.',
        ]);

        // 16. ORDERS & FULFILLMENT
        // Order 1: Delivered Repeat Order for Customer 0
        $order1 = Order::create([
            'order_number' => 'MH10291',
            'customer_id' => $customers[0]->id,
            'assigned_user_id' => $users['rahul.sales@mantraheal.com']->id,
            'order_date' => Carbon::now()->subDays(5),
            'channel' => 'direct',
            'subtotal' => 2598.00,
            'discount_amount' => 200.00,
            'coupon_code' => 'MANTRA200',
            'cgst_amount' => 143.88,
            'sgst_amount' => 143.88,
            'igst_amount' => 0.00,
            'total_tax' => 287.76,
            'shipping_charge' => 0.00,
            'grand_total' => 2398.00,
            'order_status' => 'Delivered',
            'payment_status' => 'Paid',
            'payment_method' => 'upi',
            'fulfillment_status' => 'fulfilled',
            'shipping_name' => $customers[0]->name,
            'shipping_phone' => $customers[0]->phone,
            'shipping_address_line1' => $customers[0]->address_line1,
            'shipping_city' => $customers[0]->city,
            'shipping_state' => $customers[0]->state,
            'shipping_pincode' => $customers[0]->pincode,
            'courier_name' => 'BlueDart Express',
            'tracking_number' => 'BD9827364501',
            'delivered_at' => Carbon::now()->subDays(2),
            'notes' => 'Delivered on time in tamper-evident security bag.',
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => $products[0]->id,
            'batch_id' => $batches[0]->id,
            'product_name' => $products[0]->name,
            'sku' => $products[0]->sku,
            'quantity' => 2,
            'unit_price' => 1299.00,
            'tax_rate' => 12.00,
            'total_price' => 2598.00,
            'hsn_code' => '30049011',
        ]);

        Payment::create([
            'payment_number' => 'PAY-2026-001',
            'order_id' => $order1->id,
            'customer_id' => $order1->customer_id,
            'amount' => 2398.00,
            'payment_method' => 'Prepaid UPI',
            'transaction_reference' => 'UPI9012837465',
            'payment_date' => Carbon::now()->subDays(5),
            'status' => 'Success',
            'recorded_by' => $users['rahul.sales@mantraheal.com']->id,
        ]);

        Shipment::create([
            'order_id' => $order1->id,
            'courier_name' => 'BlueDart Express',
            'tracking_number' => 'BD9827364501',
            'shipped_date' => Carbon::now()->subDays(4),
            'actual_delivery_date' => Carbon::now()->subDays(2),
            'status' => 'Delivered',
        ]);

        // Stock ledger deduct for Order 1
        StockMovement::create([
            'movement_code' => 'MOV-SALE-' . strtoupper(Str::random(6)),
            'product_id' => $products[0]->id,
            'warehouse_id' => $primaryWh->id,
            'batch_id' => $batches[0]->id,
            'movement_type' => 'sale',
            'quantity' => -2,
            'balance_after' => 124,
            'reference_type' => 'Order',
            'reference_id' => $order1->id,
            'user_id' => $users['rahul.sales@mantraheal.com']->id,
            'notes' => "Order #{$order1->order_number} fulfillment dispatch",
            'created_at' => Carbon::now()->subDays(4),
        ]);

        // Order 2: Shipped Order for Customer 1
        $order2 = Order::create([
            'order_number' => 'MH10292',
            'customer_id' => $customers[1]->id,
            'assigned_user_id' => $users['rahul.sales@mantraheal.com']->id,
            'order_date' => Carbon::now()->subDays(2),
            'channel' => 'shopify',
            'subtotal' => 749.00,
            'discount_amount' => 0.00,
            'cgst_amount' => 40.12,
            'sgst_amount' => 40.12,
            'total_tax' => 80.24,
            'shipping_charge' => 50.00,
            'grand_total' => 799.00,
            'order_status' => 'Shipped',
            'payment_status' => 'Pending',
            'payment_method' => 'cod',
            'fulfillment_status' => 'shipped',
            'shipping_name' => $customers[1]->name,
            'shipping_phone' => $customers[1]->phone,
            'shipping_address_line1' => $customers[1]->address_line1,
            'shipping_city' => $customers[1]->city,
            'shipping_state' => $customers[1]->state,
            'shipping_pincode' => $customers[1]->pincode,
            'courier_name' => 'Delhivery',
            'tracking_number' => 'DL1092837465',
            'notes' => 'Cash on Delivery parcel.',
        ]);

        OrderItem::create([
            'order_id' => $order2->id,
            'product_id' => $products[1]->id,
            'batch_id' => $batches[2]->id,
            'product_name' => $products[1]->name,
            'sku' => $products[1]->sku,
            'quantity' => 1,
            'unit_price' => 749.00,
            'tax_rate' => 12.00,
            'total_price' => 749.00,
            'hsn_code' => '30049011',
        ]);

        // Order 3: Returned / RTO Example for Customer 4
        $order3 = Order::create([
            'order_number' => 'MH10293',
            'customer_id' => $customers[4]->id,
            'assigned_user_id' => $users['rahul.sales@mantraheal.com']->id,
            'order_date' => Carbon::now()->subDays(12),
            'channel' => 'meta_ads',
            'subtotal' => 1299.00,
            'discount_amount' => 0.00,
            'total_tax' => 139.18,
            'shipping_charge' => 0.00,
            'grand_total' => 1299.00,
            'order_status' => 'RTO',
            'payment_status' => 'Pending',
            'payment_method' => 'cod',
            'fulfillment_status' => 'rto',
            'shipping_name' => $customers[4]->name,
            'shipping_phone' => $customers[4]->phone,
            'shipping_address_line1' => $customers[4]->address_line1,
            'shipping_city' => $customers[4]->city,
            'shipping_state' => $customers[4]->state,
            'shipping_pincode' => $customers[4]->pincode,
            'courier_name' => 'DTDC',
            'tracking_number' => 'DT9018273645',
        ]);

        RtoRecord::create([
            'rto_code' => 'RTO-2026-001',
            'order_id' => $order3->id,
            'customer_id' => $customers[4]->id,
            'courier_name' => 'DTDC',
            'tracking_number' => 'DT9018273645',
            'state' => 'Rajasthan',
            'city' => 'Jaipur',
            'sales_channel' => 'Meta Ads',
            'rto_initiated_date' => Carbon::now()->subDays(4),
            'reason' => 'Customer Refused on Delivery',
            'total_amount' => 1299.00,
            'status' => 'Received at Warehouse',
            'received_warehouse_id' => $primaryWh->id,
            'notes' => 'Customer was out of station during courier delivery attempt.',
        ]);

        // 17. RETURN & REFUND DEMO
        $return = OrderReturn::create([
            'return_number' => 'RET-2026-001',
            'order_id' => $order1->id,
            'customer_id' => $customers[0]->id,
            'return_date' => Carbon::now()->subDays(1),
            'reason' => 'Customer changed mind',
            'refund_action' => 'Refund',
            'restocking_warehouse_id' => $primaryWh->id,
            'status' => 'Refund / Replacement',
            'qc_status' => 'Passed',
            'processed_by' => $users['support@mantraheal.com']->id,
            'notes' => 'Customer accidentally ordered 2 jars instead of 1. Unopened box returned in pristine condition.',
        ]);

        ReturnItem::create([
            'order_return_id' => $return->id,
            'order_item_id' => $order1->items()->first()->id,
            'product_id' => $products[0]->id,
            'quantity' => 1,
            'condition_notes' => 'Factory outer seal intact and undamaged.',
        ]);

        Refund::create([
            'refund_number' => 'REF-2026-001',
            'order_id' => $order1->id,
            'order_return_id' => $return->id,
            'customer_id' => $customers[0]->id,
            'amount' => 1199.00,
            'refund_method' => 'upi',
            'transaction_reference' => 'UPI-REF-88991122',
            'status' => 'Processed',
            'processed_by' => $users['accounts@mantraheal.com']->id,
            'processed_at' => Carbon::now()->subHours(4),
            'notes' => 'Net refund after pro-rata promotional deduction.',
        ]);

        // 18. CUSTOMER SUPPORT TICKETS
        $ticket = SupportTicket::create([
            'ticket_number' => 'TICK-109283',
            'customer_id' => $customers[0]->id,
            'order_id' => $order1->id,
            'subject' => 'Recommended dosage timing for Shilajit Resin in summer months',
            'category' => 'General Inquiry',
            'priority' => 'Medium',
            'status' => 'Resolved',
            'assigned_user_id' => $users['support@mantraheal.com']->id,
            'description' => 'Customer inquired whether Shilajit Resin should be consumed with lukewarm water or raw cow milk during high ambient heat.',
            'resolved_at' => Carbon::now()->subDays(1),
        ]);

        TicketMessage::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $users['support@mantraheal.com']->id,
            'message' => 'Dear Dr. Deshmukh, during peak summer, a pea-sized portion (300-500mg) dissolved in lukewarm water or coconut water early morning on an empty stomach is recommended to prevent excessive Pitta warmth.',
            'is_internal_note' => false,
            'created_at' => Carbon::now()->subDays(1),
        ]);

        // 19. WHATSAPP TEMPLATES & COMMUNICATION LOGS
        $templatesData = [
            ['name' => 'Order Confirmation', 'slug' => 'order_confirmation', 'category' => 'TRANSACTIONAL', 'template_body' => 'Namaste {{name}}! Your MantraHeal order #{{order_number}} has been confirmed. Total amount: {{amount}}. Track your natural wellness journey anytime.'],
            ['name' => 'Dispatch & Tracking', 'slug' => 'dispatch_alert', 'category' => 'TRANSACTIONAL', 'template_body' => 'Hello {{name}}! Your pure Ayurvedic formulation is on its way. Track your consignment: {{tracking_url}} via MantraHeal Express.'],
            ['name' => 'Payment Reminder', 'slug' => 'payment_reminder', 'category' => 'UTILITY', 'template_body' => 'Dear {{name}}, your pending payment of {{amount}} for MantraHeal order #{{order_number}} is awaiting authorization.'],
            ['name' => 'Delivery Confirmation', 'slug' => 'delivery_confirmation', 'category' => 'TRANSACTIONAL', 'template_body' => 'Namaste {{name}}, your MantraHeal wellness package has been delivered! Please read the included Ayurvedic diet chart.'],
            ['name' => 'Replenishment Reminder', 'slug' => 'replenishment_reminder', 'category' => 'MARKETING', 'template_body' => 'Dear {{name}}, your 30-day course of {{name}} is nearing completion. Reorder today to maintain vital Rasayana benefits!'],
            ['name' => 'Customer Care Follow-up', 'slug' => 'care_followup', 'category' => 'MARKETING', 'template_body' => 'Hello {{name}}! How are you feeling with your MantraHeal regimen? Our certified Ayurvedic consultants are here to guide your dosage.'],
        ];

        foreach ($templatesData as $td) {
            $td['is_active'] = true;
            WhatsAppTemplate::create($td);
        }

        CommunicationLog::create([
            'customer_id' => $customers[0]->id,
            'channel' => 'WhatsApp',
            'recipient' => $customers[0]->mobile,
            'message_body' => 'Namaste Dr. Rajesh Deshmukh! Your MantraHeal order #MH10291 has been confirmed. Total amount: ₹2,398.00.',
            'status' => 'Read',
            'user_id' => $users['rahul.sales@mantraheal.com']->id,
            'created_at' => Carbon::now()->subDays(5),
        ]);

        // 20. AUDIT LOGS (REAL ACTIONS RECORDED)
        AuditLog::create([
            'user_id' => $users['admin@mantraheal.com']->id,
            'user_name' => $users['admin@mantraheal.com']->name,
            'action' => 'created',
            'auditable_type' => Product::class,
            'auditable_id' => $products[0]->id,
            'description' => "Product {$products[0]->name} created with SKU {$products[0]->sku}",
            'ip_address' => '127.0.0.1',
            'created_at' => Carbon::now()->subDays(25),
        ]);

        AuditLog::create([
            'user_id' => $users['rahul.sales@mantraheal.com']->id,
            'user_name' => $users['rahul.sales@mantraheal.com']->name,
            'action' => 'created',
            'auditable_type' => Order::class,
            'auditable_id' => $order1->id,
            'description' => "Order #{$order1->order_number} created for customer {$customers[0]->name}",
            'ip_address' => '127.0.0.1',
            'created_at' => Carbon::now()->subDays(5),
        ]);

        AuditLog::create([
            'user_id' => $users['rahul.sales@mantraheal.com']->id,
            'user_name' => $users['rahul.sales@mantraheal.com']->name,
            'action' => 'updated',
            'auditable_type' => Order::class,
            'auditable_id' => $order1->id,
            'description' => "Rahul changed order status: Shipped → Delivered",
            'old_values' => ['order_status' => 'Shipped'],
            'new_values' => ['order_status' => 'Delivered'],
            'ip_address' => '127.0.0.1',
            'created_at' => Carbon::now()->subDays(2),
        ]);
    }
}
