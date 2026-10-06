<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique()->index();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->dateTime('order_date')->index();
            $table->string('channel')->default('Website')->index(); // Website, Shopify, Meta Ads, Phone/Call, WhatsApp, Offline
            $table->string('shopify_order_id')->nullable()->index();
            
            // Financials
            $table->decimal('subtotal', 12, 2)->default(0.00);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->string('coupon_code')->nullable();
            $table->decimal('cgst_amount', 10, 2)->default(0.00);
            $table->decimal('sgst_amount', 10, 2)->default(0.00);
            $table->decimal('igst_amount', 10, 2)->default(0.00);
            $table->decimal('total_tax', 10, 2)->default(0.00);
            $table->decimal('shipping_charge', 10, 2)->default(0.00);
            $table->decimal('grand_total', 12, 2)->default(0.00);
            
            // Statuses
            $table->enum('order_status', [
                'New',
                'Confirmed',
                'Processing',
                'Packed',
                'Shipped',
                'Out for Delivery',
                'Delivered',
                'Cancelled',
                'Returned',
                'Refunded',
                'RTO'
            ])->default('New')->index();

            $table->enum('payment_status', [
                'Pending',
                'Paid',
                'Partially Paid',
                'Failed',
                'Refunded'
            ])->default('Pending')->index();

            $table->string('payment_method')->default('COD')->index();
            $table->string('fulfillment_status')->default('Unfulfilled')->index();
            
            // Shipping Address snapshot
            $table->string('shipping_name')->nullable();
            $table->string('shipping_phone')->nullable();
            $table->string('shipping_address_line1')->nullable();
            $table->string('shipping_address_line2')->nullable();
            $table->string('shipping_city')->nullable()->index();
            $table->string('shipping_state')->nullable()->index();
            $table->string('shipping_pincode', 10)->nullable()->index();
            
            // Logistics & Tracking
            $table->string('courier_name')->nullable()->index();
            $table->string('tracking_number')->nullable()->index();
            $table->string('shiprocket_shipment_id')->nullable()->index();
            $table->dateTime('delivered_at')->nullable();
            
            // Assignment & Notes
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('batches')->nullOnDelete();
            $table->string('product_name');
            $table->string('sku')->index();
            $table->string('hsn_code')->nullable();
            $table->decimal('unit_price', 10, 2);
            $table->integer('quantity');
            $table->decimal('tax_percent', 5, 2)->default(12.00);
            $table->decimal('tax_amount', 10, 2)->default(0.00);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->decimal('total_price', 10, 2);
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique()->index();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('payment_method'); // COD, Razorpay, Prepaid UPI, Net Banking, Bank Transfer
            $table->string('transaction_reference')->nullable()->index();
            $table->decimal('amount', 10, 2);
            $table->dateTime('payment_date');
            $table->enum('status', ['Pending', 'Success', 'Failed', 'Refunded'])->default('Pending')->index();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('courier_name');
            $table->string('tracking_number')->index();
            $table->string('awb_code')->nullable()->index();
            $table->dateTime('shipped_date')->nullable();
            $table->dateTime('expected_delivery_date')->nullable();
            $table->dateTime('actual_delivery_date')->nullable();
            $table->string('status')->default('Shipped')->index();
            $table->string('shipping_label_url')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
