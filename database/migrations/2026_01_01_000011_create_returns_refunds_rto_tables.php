<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number')->unique()->index();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->date('return_date')->index();
            $table->enum('reason', [
                'Damaged',
                'Wrong product',
                'Customer changed mind',
                'Product issue',
                'Delivery issue',
                'Other'
            ])->index();
            
            // Workflow: Return Requested -> Approved -> Pickup -> Received -> QC -> Refund / Replacement
            $table->enum('status', [
                'Return Requested',
                'Approved',
                'Pickup',
                'Received',
                'QC',
                'Refund / Replacement',
                'Rejected'
            ])->default('Return Requested')->index();

            $table->enum('qc_status', ['Pending', 'Passed', 'Failed'])->default('Pending')->index();
            $table->foreignId('restocking_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->enum('refund_action', ['Refund', 'Replacement', 'Store Credit'])->default('Refund');
            $table->text('notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_return_id')->constrained('order_returns')->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('quantity');
            $table->string('reason')->nullable();
            $table->text('condition_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->string('refund_number')->unique()->index();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('order_return_id')->nullable()->constrained('order_returns')->nullOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('refund_method')->default('Original Payment Method');
            $table->string('transaction_reference')->nullable()->index();
            $table->enum('status', ['Pending', 'Processed', 'Failed'])->default('Pending')->index();
            $table->text('notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('rto_records', function (Blueprint $table) {
            $table->id();
            $table->string('rto_code')->unique()->index();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('courier_name')->index();
            $table->string('tracking_number')->index();
            $table->date('rto_initiated_date')->index();
            $table->date('rto_delivered_date')->nullable();
            $table->string('reason')->index();
            $table->string('state')->index();
            $table->string('city')->index();
            $table->string('sales_channel')->default('Website')->index();
            $table->decimal('total_amount', 10, 2);
            $table->enum('status', ['In Transit', 'Received at Warehouse', 'QC Completed'])->default('In Transit')->index();
            $table->foreignId('received_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rto_records');
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('return_items');
        Schema::dropIfExists('order_returns');
    }
};
