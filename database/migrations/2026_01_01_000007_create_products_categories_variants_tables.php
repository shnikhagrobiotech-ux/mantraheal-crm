<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_code')->unique()->index();
            $table->string('sku')->unique()->index();
            $table->string('name')->index();
            $table->string('slug')->unique();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('brand')->default('MantraHeal')->index();
            $table->text('description')->nullable();
            $table->decimal('mrp', 10, 2);
            $table->decimal('selling_price', 10, 2);
            $table->decimal('purchase_price', 10, 2);
            $table->decimal('gst_percent', 5, 2)->default(12.00);
            $table->string('hsn_code')->nullable()->index();
            $table->string('barcode')->nullable()->index();
            $table->integer('minimum_stock')->default(10);
            $table->integer('reorder_level')->default(25);
            $table->boolean('is_active')->default(true)->index();
            $table->string('image_path')->nullable();
            $table->timestamps();
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('variant_name'); // e.g. 500ml, 1000ml, 60 Capsules, 120 Capsules
            $table->string('sku')->unique()->index();
            $table->string('barcode')->nullable();
            $table->decimal('mrp', 10, 2);
            $table->decimal('selling_price', 10, 2);
            $table->decimal('purchase_price', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};
