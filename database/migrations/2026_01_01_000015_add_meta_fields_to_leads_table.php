<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('meta_lead_id')->nullable()->index();
            $table->string('meta_form_id')->nullable();
            $table->string('meta_campaign_name')->nullable();
            $table->string('meta_ad_name')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'meta_lead_id',
                'meta_form_id',
                'meta_campaign_name',
                'meta_ad_name',
            ]);
        });
    }
};
