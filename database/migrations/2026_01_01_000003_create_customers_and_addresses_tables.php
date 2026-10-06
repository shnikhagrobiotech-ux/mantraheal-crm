<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code')->unique()->index();
            $table->string('name')->index();
            $table->string('mobile')->index();
            $table->string('whatsapp')->nullable()->index();
            $table->string('email')->nullable()->index();
            $table->string('customer_source')->default('Website')->index();
            $table->string('customer_type')->default('Retail')->index();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('active')->index();
            $table->string('address_line1')->nullable();
            $table->string('city')->nullable()->index();
            $table->string('state')->nullable()->index();
            $table->string('pincode', 10)->nullable()->index();
            $table->text('tags')->nullable();
            $table->text('notes')->nullable();
            
            // Computed Customer 360 KPIs cached on record
            $table->integer('total_orders')->default(0);
            $table->decimal('total_spend', 12, 2)->default(0.00);
            $table->decimal('average_order_value', 10, 2)->default(0.00);
            $table->decimal('outstanding_amount', 10, 2)->default(0.00);
            $table->timestamp('first_order_at')->nullable();
            $table->timestamp('last_order_at')->nullable();
            $table->timestamps();
        });

        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('type')->default('shipping'); // shipping, billing
            $table->string('address_line1');
            $table->string('address_line2')->nullable();
            $table->string('landmark')->nullable();
            $table->string('city')->index();
            $table->string('state')->index();
            $table->string('pincode', 10)->index();
            $table->string('country')->default('India');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('customer_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('color')->default('#0d9488');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_tags');
        Schema::dropIfExists('customer_addresses');
        Schema::dropIfExists('customers');
    }
};
