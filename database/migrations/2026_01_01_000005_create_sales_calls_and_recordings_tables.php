<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->cascadeOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Sales Employee
            $table->enum('direction', ['outgoing', 'incoming'])->default('outgoing')->index();
            $table->dateTime('call_datetime')->index();
            $table->integer('duration_seconds')->default(0); // in seconds
            $table->string('status')->default('Completed')->index(); // Completed, Missed, Rejected, Busy
            $table->string('outcome')->default('Connected')->index();
            // Connected, Interested, Order Taken, Follow-up Required, Not Interested, Busy, No Answer, Wrong Number, Complaint, Payment Discussion
            $table->text('notes')->nullable();
            $table->date('follow_up_date')->nullable()->index();
            $table->string('recording_path')->nullable();
            $table->timestamps();
        });

        Schema::create('call_recordings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_call_id')->constrained('sales_calls')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('file_path'); // stored under storage/app/call-recordings/
            $table->string('file_name');
            $table->string('mime_type')->default('audio/wav');
            $table->bigInteger('file_size')->default(0);
            $table->integer('duration_seconds')->default(0);
            $table->boolean('is_protected')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('call_recordings');
        Schema::dropIfExists('sales_calls');
    }
};
