<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('category')->default('TRANSACTIONAL'); // MARKETING, UTILITY, TRANSACTIONAL
            $table->text('template_body');
            $table->json('variables')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('communication_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->cascadeOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('channel', ['WhatsApp', 'SMS', 'Email'])->default('WhatsApp')->index();
            $table->foreignId('template_id')->nullable()->constrained('whatsapp_templates')->nullOnDelete();
            $table->string('recipient')->index();
            $table->string('subject')->nullable();
            $table->text('message_body');
            $table->enum('status', ['Sent', 'Delivered', 'Read', 'Failed'])->default('Sent')->index();
            $table->string('external_id')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('marketing_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->unique();
            $table->string('channel_type')->default('Digital'); // Social, Search, Direct, Referral, Offline
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('marketing_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->foreignId('marketing_source_id')->constrained('marketing_sources')->cascadeOnDelete();
            $table->decimal('budget', 12, 2)->default(0.00);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_campaigns');
        Schema::dropIfExists('marketing_sources');
        Schema::dropIfExists('communication_logs');
        Schema::dropIfExists('whatsapp_templates');
    }
};
