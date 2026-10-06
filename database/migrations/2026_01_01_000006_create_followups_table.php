<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('followups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->cascadeOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Assigned Employee
            $table->date('due_date')->index();
            $table->time('due_time')->nullable();
            $table->string('reason');
            $table->enum('priority', ['Low', 'Medium', 'High'])->default('Medium')->index();
            $table->enum('status', ['Pending', 'Completed', 'Rescheduled', 'Missed', 'Cancelled'])->default('Pending')->index();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('followups');
    }
};
