<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique()->index();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('subject')->index();
            $table->enum('category', ['Delivery', 'Product Quality', 'Payment', 'Return/Refund', 'General Inquiry', 'Complaint'])->default('General Inquiry')->index();
            $table->enum('priority', ['Low', 'Medium', 'High', 'Urgent'])->default('Medium')->index();
            $table->enum('status', ['Open', 'In Progress', 'Resolved', 'Closed'])->default('Open')->index();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('description');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_ticket_id')->constrained('support_tickets')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('message');
            $table->boolean('is_internal_note')->default(false);
            $table->string('attachment_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_messages');
        Schema::dropIfExists('support_tickets');
    }
};
