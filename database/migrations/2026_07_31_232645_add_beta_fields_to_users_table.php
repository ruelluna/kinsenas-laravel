<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('beta_enrolled_at')->nullable()->after('is_platform_admin');
            $table->boolean('beta_launch_discount_eligible')->default(false)->after('beta_enrolled_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['beta_enrolled_at', 'beta_launch_discount_eligible']);
        });
    }
};
