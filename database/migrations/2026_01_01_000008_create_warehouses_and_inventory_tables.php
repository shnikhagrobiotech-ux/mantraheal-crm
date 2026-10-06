<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->index();
            $table->string('name')->unique();
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address');
            $table->string('city')->index();
            $table->string('state')->index();
            $table->string('pincode', 10);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_number')->index();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->date('manufacturing_date');
            $table->date('expiry_date')->index();
            $table->decimal('cost_price', 10, 2);
            $table->decimal('selling_price', 10, 2);
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->integer('initial_quantity')->default(0);
            $table->integer('current_quantity')->default(0);
            $table->timestamps();

            $table->unique(['batch_number', 'product_id', 'warehouse_id']);
        });

        Schema::create('stock_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->integer('quantity')->default(0);
            $table->integer('reserved_quantity')->default(0); // for orders processing
            $table->timestamps();

            $table->unique(['product_id', 'product_variant_id', 'warehouse_id'], 'stock_balance_unique');
        });

        // Immutable Stock Ledger Table
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->string('movement_code')->unique()->index();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('batches')->nullOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            
            // Opening Stock, Purchase, Customer Return, Stock Transfer In, Sales, Damage, Stock Transfer Out, Adjustment
            $table->enum('movement_type', [
                'opening',
                'purchase',
                'customer_return',
                'transfer_in',
                'sale',
                'damage',
                'transfer_out',
                'adjustment'
            ])->index();

            $table->integer('quantity'); // Positive for addition, negative for deduction
            $table->integer('balance_after');
            $table->string('reference_type')->nullable()->index(); // Order, GRN, Adjustment, Return, Transfer
            $table->unsignedBigInteger('reference_id')->nullable()->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('stock_balances');
        Schema::dropIfExists('batches');
        Schema::dropIfExists('warehouses');
    }
};
