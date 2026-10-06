<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('supplier_code')->unique()->index();
            $table->string('name')->index();
            $table->string('contact_person')->nullable();
            $table->string('phone')->index();
            $table->string('email')->nullable();
            $table->string('gstin')->nullable()->index();
            $table->text('address');
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode', 10)->nullable();
            $table->string('payment_terms')->default('Net 30');
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique()->index();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->date('po_date')->index();
            $table->date('expected_delivery_date')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0.00);
            $table->decimal('tax_amount', 10, 2)->default(0.00);
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->enum('status', ['Draft', 'Ordered', 'Partial', 'Received', 'Cancelled'])->default('Draft')->index();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->integer('quantity');
            $table->decimal('rate', 10, 2);
            $table->decimal('tax_percent', 5, 2)->default(12.00);
            $table->decimal('tax_amount', 10, 2)->default(0.00);
            $table->decimal('total_amount', 10, 2);
            $table->integer('received_quantity')->default(0);
            $table->timestamps();
        });

        Schema::create('goods_received_notes', function (Blueprint $table) {
            $table->id();
            $table->string('grn_number')->unique()->index();
            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->date('grn_date')->index();
            $table->string('invoice_number')->nullable()->index();
            $table->date('invoice_date')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['Draft', 'Approved', 'Rejected'])->default('Draft')->index();
            $table->timestamps();
        });

        Schema::create('grn_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_received_note_id')->constrained('goods_received_notes')->cascadeOnDelete();
            $table->foreignId('purchase_order_item_id')->nullable()->constrained('purchase_order_items')->nullOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->integer('ordered_quantity')->default(0);
            $table->integer('received_quantity')->default(0);
            $table->integer('damaged_quantity')->default(0);
            $table->integer('accepted_quantity')->default(0);
            $table->string('batch_number')->nullable();
            $table->date('manufacturing_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('unit_cost', 10, 2)->default(0.00);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grn_items');
        Schema::dropIfExists('goods_received_notes');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('suppliers');
    }
};
