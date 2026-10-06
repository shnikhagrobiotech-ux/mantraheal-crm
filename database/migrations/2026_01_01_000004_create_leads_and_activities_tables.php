<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('lead_code')->unique()->index();
            $table->string('name')->index();
            $table->string('mobile')->index();
            $table->string('whatsapp')->nullable()->index();
            $table->string('email')->nullable()->index();
            $table->string('city')->nullable()->index();
            $table->string('state')->nullable()->index();
            $table->string('pincode', 10)->nullable();
            $table->string('source')->default('Website')->index();
            $table->string('stage')->default('New')->index(); 
            // New, Contacted, Interested, Follow-up, Quotation Sent, Order Confirmed, Payment Pending, Won, Lost
            $table->string('lost_reason')->nullable()->index();
            // Price, Not Interested, Competitor, No Response, Wrong Number, Product Unavailable, Timing, Other
            $table->decimal('estimated_value', 10, 2)->default(0.00);
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('next_followup_at')->nullable()->index();
            $table->text('notes')->nullable();
            $table->foreignId('converted_to_customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->unsignedBigInteger('converted_to_order_id')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('lead_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type')->default('note'); // note, call, stage_change, email, whatsapp, assignment
            $table->text('description');
            $table->string('old_stage')->nullable();
            $table->string('new_stage')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_activities');
        Schema::dropIfExists('leads');
        Schema::dropIfExists('lead_sources');
    }
};
