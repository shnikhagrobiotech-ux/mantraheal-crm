<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->foreignId('role_id')->nullable()->after('password')->constrained('roles')->nullOnDelete();
            $table->string('role_slug')->default('sales_executive')->after('role_id')->index();
            $table->string('designation')->nullable()->after('role_slug');
            $table->string('status')->default('active')->after('designation')->index();
            $table->timestamp('last_login_at')->nullable()->after('status');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn([
                'phone',
                'role_id',
                'role_slug',
                'designation',
                'status',
                'last_login_at',
                'last_login_ip'
            ]);
        });
    }
};
